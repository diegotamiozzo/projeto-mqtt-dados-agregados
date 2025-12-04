<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgregarLeituras extends Command
{
    protected $signature = 'leituras:agregar {--periodo=hora : Período de agregação (hora ou dia)}';
    protected $description = 'Agrega leituras de equipamentos em dados_agregados com cálculo ponderado';

    private const BATCH_SIZE = 500;

    public function handle()
    {
        $periodo = $this->option('periodo');
        $this->info("=== Iniciando agregação por {$periodo} (Modo Corrigido) ===");

        if (!in_array($periodo, ['hora', 'dia'])) {
            $this->error('Período inválido. Use: hora ou dia');
            return 1;
        }

        // Fixamos o timestamp de corte para garantir consistência em todas as queries
        $timestampCorte = now();

        try {
            $this->processarCorrentes($periodo);
            $this->processarSimples('temperaturas', 'temperatura', $periodo);
            $this->processarSimples('umidades', 'umidade', $periodo);
            $this->processarGrandezasEletricas($periodo);

            DB::transaction(function () use ($timestampCorte) {
                $this->marcarComoAgregado($timestampCorte);
                $this->limparRegistrosAntigos();
            });

            $this->info("\n✓ Agregação finalizada com sucesso!");
            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Erro durante agregação: " . $e->getMessage());
            \Log::error($e);
            return 1;
        }
    }

    private function getFormatoSQL($periodo): string
    {
        return match($periodo) {
            'hora' => '%Y-%m-%d %H:00:00',
            'dia' => '%Y-%m-%d 00:00:00',
        };
    }

    /**
     * Calcula a média ponderada entre o valor já salvo e o novo lote
     */
    private function calcularMediaPonderada($mediaAntiga, $countAntigo, $mediaNova, $countNovo)
    {
        if ($countAntigo + $countNovo == 0) return 0;
        return (($mediaAntiga * $countAntigo) + ($mediaNova * $countNovo)) / ($countAntigo + $countNovo);
    }

    /**
     * Helper para calcular o fim do período baseado no início e no tipo
     */
    private function getFimPeriodo($inicio, $periodo)
    {
        $dt = Carbon::parse($inicio);
        return $periodo === 'hora' ? $dt->endOfHour()->toDateTimeString() : $dt->endOfDay()->toDateTimeString();
    }

    private function processarCorrentes(string $periodo): void
    {
        $this->info("\n[1/4] Agregando correntes...");
        $formato = $this->getFormatoSQL($periodo);

        $tipos = [
            ['tabela' => 'corrente_brunidores', 'prefixo' => 'brunidores'],
            ['tabela' => 'corrente_descascadores', 'prefixo' => 'descascadores'],
            ['tabela' => 'corrente_polidores', 'prefixo' => 'polidores']
        ];

        foreach ($tipos as $tipo) {
            $tabela = $tipo['tabela'];
            $prefixo = $tipo['prefixo'];

            // Query otimizada para pegar apenas os não agregados
            $sql = "
                SELECT
                    id_cliente,
                    id_equipamento,
                    DATE_FORMAT(timestamp, '{$formato}') AS periodo_inicio,
                    AVG(corrente) AS media,
                    MAX(corrente) AS maximo,
                    MIN(corrente) AS minimo,
                    COUNT(*) AS total
                FROM {$tabela}
                WHERE agregado = 0
                GROUP BY id_cliente, id_equipamento, periodo_inicio
            ";

            $leituras = DB::select($sql);

            if (empty($leituras)) {
                continue;
            }

            $this->upsertLote($leituras, $periodo, function($dado, $existente) use ($prefixo, $tabela, $periodo) {
                // OTIMIZAÇÃO: Usar BETWEEN permite uso de índice no timestamp, evitando lentidão
                $fimPeriodo = $this->getFimPeriodo($dado->periodo_inicio, $periodo);
                
                $ultima = DB::selectOne("
                    SELECT corrente FROM {$tabela} 
                    WHERE id_cliente = ? AND id_equipamento = ? 
                    AND timestamp BETWEEN ? AND ?
                    ORDER BY timestamp DESC LIMIT 1
                ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio, $fimPeriodo]);
                
                $media = $dado->media;
                $max   = $dado->maximo;
                $min   = $dado->minimo;

                // CORREÇÃO: Se já existe registro, faz o merge matemático
                if ($existente) {
                    $colMedia = "corrente_{$prefixo}_media";
                    $colMax   = "corrente_{$prefixo}_max";
                    $colMin   = "corrente_{$prefixo}_min";

                    $media = $this->calcularMediaPonderada(
                        $existente->$colMedia, 
                        $existente->registros_contagem, 
                        $dado->media, 
                        $dado->total
                    );
                    $max = max($existente->$colMax, $dado->maximo);
                    $min = min($existente->$colMin, $dado->minimo);
                }

                return [
                    "corrente_{$prefixo}_media" => $media,
                    "corrente_{$prefixo}_max" => $max,
                    "corrente_{$prefixo}_min" => $min,
                    "corrente_{$prefixo}_ultima" => $ultima->corrente ?? null,
                ];
            });
            
            $this->info("  ✓ {$tabela} processada.");
        }
    }

    private function processarSimples(string $tabela, string $coluna, string $periodo): void
    {
        $this->info("  → Processando {$tabela}...");
        $formato = $this->getFormatoSQL($periodo);

        $leituras = DB::select("
            SELECT
                id_cliente,
                id_equipamento,
                DATE_FORMAT(timestamp, '{$formato}') AS periodo_inicio,
                AVG({$coluna}) AS media,
                MAX({$coluna}) AS maximo,
                MIN({$coluna}) AS minimo,
                COUNT(*) AS total
            FROM {$tabela}
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        if (empty($leituras)) return;

        $this->upsertLote($leituras, $periodo, function($dado, $existente) use ($tabela, $coluna, $periodo) {
            // OTIMIZAÇÃO: Usar BETWEEN
            $fimPeriodo = $this->getFimPeriodo($dado->periodo_inicio, $periodo);

            $ultima = DB::selectOne("
                SELECT {$coluna} FROM {$tabela} 
                WHERE id_cliente = ? AND id_equipamento = ? 
                AND timestamp BETWEEN ? AND ?
                ORDER BY timestamp DESC LIMIT 1
            ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio, $fimPeriodo]);

            $media = $dado->media;
            $max   = $dado->maximo;
            $min   = $dado->minimo;

            if ($existente) {
                $colMedia = "{$coluna}_media";
                $colMax   = "{$coluna}_max";
                $colMin   = "{$coluna}_min";

                $media = $this->calcularMediaPonderada(
                    $existente->$colMedia, 
                    $existente->registros_contagem, 
                    $dado->media, 
                    $dado->total
                );
                $max = max($existente->$colMax, $dado->maximo);
                $min = min($existente->$colMin, $dado->minimo);
            }

            return [
                "{$coluna}_media" => $media,
                "{$coluna}_max" => $max,
                "{$coluna}_min" => $min,
                "{$coluna}_ultima" => $ultima->{$coluna} ?? null,
            ];
        });
    }

    private function processarGrandezasEletricas(string $periodo): void
    {
        $this->info("\n[4/4] Agregando grandezas elétricas...");
        $formato = $this->getFormatoSQL($periodo);

        $leituras = DB::select("
            SELECT
                id_cliente,
                id_equipamento,
                DATE_FORMAT(timestamp, '{$formato}') AS periodo_inicio,
                AVG(tensao_r) as tr_avg, MAX(tensao_r) as tr_max, MIN(tensao_r) as tr_min,
                AVG(tensao_s) as ts_avg, MAX(tensao_s) as ts_max, MIN(tensao_s) as ts_min,
                AVG(tensao_t) as tt_avg, MAX(tensao_t) as tt_max, MIN(tensao_t) as tt_min,
                AVG(corrente_r) as cr_avg, MAX(corrente_r) as cr_max, MIN(corrente_r) as cr_min,
                AVG(corrente_s) as cs_avg, MAX(corrente_s) as cs_max, MIN(corrente_s) as cs_min,
                AVG(corrente_t) as ct_avg, MAX(corrente_t) as ct_max, MIN(corrente_t) as ct_min,
                AVG(potencia_ativa) as pa_avg, MAX(potencia_ativa) as pa_max, MIN(potencia_ativa) as pa_min,
                AVG(potencia_reativa) as pr_avg, MAX(potencia_reativa) as pr_max, MIN(potencia_reativa) as pr_min,
                AVG(potencia_aparente) as pap_avg, MAX(potencia_aparente) as pap_max, MIN(potencia_aparente) as pap_min,
                AVG(fator_potencia) as fp_avg, MAX(fator_potencia) as fp_max, MIN(fator_potencia) as fp_min,
                COUNT(*) AS total
            FROM grandezas_eletricas
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        if (empty($leituras)) return;

        $this->upsertLote($leituras, $periodo, function($dado, $existente) use ($periodo) {
            // OTIMIZAÇÃO: Usar BETWEEN
            $fimPeriodo = $this->getFimPeriodo($dado->periodo_inicio, $periodo);

            $ultima = DB::selectOne("
                SELECT tensao_r, tensao_s, tensao_t, corrente_r, corrente_s, corrente_t,
                       potencia_ativa, potencia_reativa, potencia_aparente, fator_potencia
                FROM grandezas_eletricas
                WHERE id_cliente = ? AND id_equipamento = ? 
                AND timestamp BETWEEN ? AND ?
                ORDER BY timestamp DESC LIMIT 1
            ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio, $fimPeriodo]);

            $campos = [
                'tensao_r' => 'tr', 'tensao_s' => 'ts', 'tensao_t' => 'tt',
                'corrente_r' => 'cr', 'corrente_s' => 'cs', 'corrente_t' => 'ct',
                'potencia_ativa' => 'pa', 'potencia_reativa' => 'pr', 'potencia_aparente' => 'pap',
                'fator_potencia' => 'fp'
            ];

            $resultado = [];

            foreach ($campos as $campoBD => $alias) {
                $media = $dado->{$alias.'_avg'};
                $max   = $dado->{$alias.'_max'};
                $min   = $dado->{$alias.'_min'};

                if ($existente) {
                    $media = $this->calcularMediaPonderada(
                        $existente->{$campoBD.'_media'},
                        $existente->registros_contagem,
                        $media,
                        $dado->total
                    );
                    $max = max($existente->{$campoBD.'_max'}, $max);
                    $min = min($existente->{$campoBD.'_min'}, $min);
                }

                $resultado["{$campoBD}_media"]  = $media;
                $resultado["{$campoBD}_max"]    = $max;
                $resultado["{$campoBD}_min"]    = $min;
                $resultado["{$campoBD}_ultima"] = $ultima->$campoBD ?? null;
            }

            return $resultado;
        });
    }

    private function upsertLote(array $novosDados, string $periodo, callable $mapCallback): void
    {
        $chunks = array_chunk($novosDados, self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $chavesBusca = [];
            foreach ($chunk as $row) {
                $chavesBusca[] = "{$row->id_cliente}-{$row->id_equipamento}-{$row->periodo_inicio}";
            }

            // Busca registros existentes para fazer o MERGE dos valores
            $existentes = DB::table('dados_agregados')
                ->whereIn(DB::raw("CONCAT(id_cliente, '-', id_equipamento, '-', periodo_inicio)"), $chavesBusca)
                ->get()
                ->keyBy(function ($item) {
                    return "{$item->id_cliente}-{$item->id_equipamento}-{$item->periodo_inicio}";
                });

            $upsertData = [];

            foreach ($chunk as $dado) {
                $key = "{$dado->id_cliente}-{$dado->id_equipamento}-{$dado->periodo_inicio}";
                $registroExistente = $existentes->get($key);

                $inicio = Carbon::parse($dado->periodo_inicio);
                $fim = $periodo === 'hora' ? $inicio->copy()->addHour() : $inicio->copy()->addDay();

                // A MÁGICA ACONTECE AQUI: Passamos o registro existente para o callback fazer a média ponderada
                $camposEspecificos = $mapCallback($dado, $registroExistente);

                $linha = array_merge([
                    'id_cliente' => $dado->id_cliente,
                    'id_equipamento' => $dado->id_equipamento,
                    'periodo_inicio' => $dado->periodo_inicio,
                    'periodo_fim' => $fim->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ], $camposEspecificos);

                if ($registroExistente) {
                    $linha['registros_contagem'] = $registroExistente->registros_contagem + $dado->total;
                    $linha['created_at'] = $registroExistente->created_at;
                } else {
                    $linha['registros_contagem'] = $dado->total;
                    $linha['created_at'] = now()->toDateTimeString();
                }

                $upsertData[] = $linha;
            }

            if (!empty($upsertData)) {
                DB::table('dados_agregados')->upsert(
                    $upsertData, 
                    ['id_cliente', 'id_equipamento', 'periodo_inicio'],
                    array_keys(reset($upsertData))
                );
            }
        }
    }

    private function marcarComoAgregado($timestampCorte): void
    {
        $this->info("\n[5/5] Marcando registros processados...");

        $tabelas = [
            'corrente_brunidores', 'corrente_descascadores', 'corrente_polidores',
            'temperaturas', 'umidades', 'grandezas_eletricas'
        ];

        foreach ($tabelas as $tabela) {
            $count = DB::table($tabela)
                ->where('agregado', 0)
                ->where('timestamp', '<=', $timestampCorte)
                ->update(['agregado' => 1]);
            
            if ($count > 0) {
                $this->info("  → {$tabela}: {$count} registros marcados");
            }
        }
    }

    private function limparRegistrosAntigos(): void 
    {
        $this->info("\nLimpando registros antigos...");
        $limite = Carbon::now()->subDays(30)->toDateTimeString();
        
        $tabelas = [
            'corrente_brunidores', 'corrente_descascadores', 'corrente_polidores',
            'temperaturas', 'umidades', 'grandezas_eletricas'
        ];

        foreach ($tabelas as $tabela) {
            do {
                $count = DB::delete("
                    DELETE FROM {$tabela} 
                    WHERE timestamp < ? AND agregado = 1 
                    LIMIT 5000
                ", [$limite]);
                
                if ($count > 0) {
                     $this->info("  → {$tabela}: lote de {$count} removido");
                }
            } while ($count >= 5000);
        }
    }
}