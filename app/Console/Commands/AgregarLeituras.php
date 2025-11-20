<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgregarLeituras extends Command
{
    protected $signature = 'leituras:agregar {--periodo=hora : Período de agregação (hora ou dia)}';
    protected $description = 'Agrega leituras de equipamentos em dados_agregados com alta performance';

    // Define o tamanho do lote para inserções em massa
    private const BATCH_SIZE = 500;

    public function handle()
    {
        $periodo = $this->option('periodo');
        $this->info("=== Iniciando agregação por {$periodo} (Modo Otimizado) ===");

        if (!in_array($periodo, ['hora', 'dia'])) {
            $this->error('Período inválido. Use: hora ou dia');
            return 1;
        }

        // Captura o momento do início para garantir consistência na marcação posterior
        $timestampCorte = now();

        try {
            // Transações separadas por operação reduzem Deadlocks e Long Locks
            $this->processarCorrentes($periodo);
            $this->processarSimples('temperaturas', 'temperatura', $periodo);
            $this->processarSimples('umidades', 'umidade', $periodo);
            $this->processarGrandezasEletricas($periodo);

            // Marcação e Limpeza
            DB::transaction(function () use ($timestampCorte) {
                $this->marcarComoAgregado($timestampCorte);
                $this->limparRegistrosAntigos();
            });

            $this->info("\n✓ Agregação finalizada com sucesso!");
            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Erro durante agregação: " . $e->getMessage());
            // Logar o erro completo para debug
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
     * Processa as tabelas de correntes (brunidores, descascadores, polidores)
     */
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

            DB::transaction(function () use ($tabela, $prefixo, $formato, $periodo) {
                // Seleciona os dados agrupados
                $leituras = DB::select("
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
                ");

                if (empty($leituras)) {
                    return;
                }

                $this->upsertLote($leituras, $periodo, function($dado, $existente) use ($prefixo, $tabela, $formato) {
                    // Busca última leitura de forma eficiente apenas se necessário
                    $ultima = DB::selectOne("
                        SELECT corrente FROM {$tabela} 
                        WHERE id_cliente = ? AND id_equipamento = ? AND DATE_FORMAT(timestamp, '{$formato}') = ? 
                        ORDER BY timestamp DESC LIMIT 1
                    ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);
                    
                    return [
                        "corrente_{$prefixo}_media" => $dado->media,
                        "corrente_{$prefixo}_max" => $dado->maximo,
                        "corrente_{$prefixo}_min" => $dado->minimo,
                        "corrente_{$prefixo}_ultima" => $ultima->corrente ?? null,
                    ];
                });
                
                $this->info("  ✓ {$tabela} processada.");
            });
        }
    }

    /**
     * Processa tabelas com estrutura simples (Temperatura, Umidade)
     * Reutiliza lógica para evitar duplicação de código
     */
    private function processarSimples(string $tabela, string $coluna, string $periodo): void
    {
        $this->info("  → Processando {$tabela}...");
        $formato = $this->getFormatoSQL($periodo);

        DB::transaction(function () use ($tabela, $coluna, $formato, $periodo) {
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

            $this->upsertLote($leituras, $periodo, function($dado) use ($tabela, $coluna, $formato) {
                $ultima = DB::selectOne("
                    SELECT {$coluna} FROM {$tabela} 
                    WHERE id_cliente = ? AND id_equipamento = ? AND DATE_FORMAT(timestamp, '{$formato}') = ? 
                    ORDER BY timestamp DESC LIMIT 1
                ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

                return [
                    "{$coluna}_media" => $dado->media,
                    "{$coluna}_max" => $dado->maximo,
                    "{$coluna}_min" => $dado->minimo,
                    "{$coluna}_ultima" => $ultima->{$coluna} ?? null,
                ];
            });
        });
    }

    private function processarGrandezasEletricas(string $periodo): void
    {
        $this->info("\n[4/4] Agregando grandezas elétricas...");
        $formato = $this->getFormatoSQL($periodo);

        DB::transaction(function () use ($formato, $periodo) {
            // Query otimizada para pegar as agregações
            // Nota: Adicionei índices nas colunas de seleção se não existirem
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
                    AVG(fator_potencia) as fp_avg, MAX(fator_potencia) as fp_max, MIN(fator_potencia) as fp_min,
                    COUNT(*) AS total
                FROM grandezas_eletricas
                WHERE agregado = 0
                GROUP BY id_cliente, id_equipamento, periodo_inicio
            ");

            if (empty($leituras)) return;

            $this->upsertLote($leituras, $periodo, function($dado) use ($formato) {
                // Busca a linha completa da última leitura
                $ultima = DB::selectOne("
                    SELECT tensao_r, tensao_s, tensao_t, corrente_r, corrente_s, corrente_t,
                           potencia_ativa, potencia_reativa, fator_potencia
                    FROM grandezas_eletricas
                    WHERE id_cliente = ? AND id_equipamento = ? AND DATE_FORMAT(timestamp, '{$formato}') = ?
                    ORDER BY timestamp DESC LIMIT 1
                ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

                return [
                    'tensao_r_media' => $dado->tr_avg, 'tensao_r_max' => $dado->tr_max, 'tensao_r_min' => $dado->tr_min, 'tensao_r_ultima' => $ultima->tensao_r ?? null,
                    'tensao_s_media' => $dado->ts_avg, 'tensao_s_max' => $dado->ts_max, 'tensao_s_min' => $dado->ts_min, 'tensao_s_ultima' => $ultima->tensao_s ?? null,
                    'tensao_t_media' => $dado->tt_avg, 'tensao_t_max' => $dado->tt_max, 'tensao_t_min' => $dado->tt_min, 'tensao_t_ultima' => $ultima->tensao_t ?? null,
                    'corrente_r_media' => $dado->cr_avg, 'corrente_r_max' => $dado->cr_max, 'corrente_r_min' => $dado->cr_min, 'corrente_r_ultima' => $ultima->corrente_r ?? null,
                    'corrente_s_media' => $dado->cs_avg, 'corrente_s_max' => $dado->cs_max, 'corrente_s_min' => $dado->cs_min, 'corrente_s_ultima' => $ultima->corrente_s ?? null,
                    'corrente_t_media' => $dado->ct_avg, 'corrente_t_max' => $dado->ct_max, 'corrente_t_min' => $dado->ct_min, 'corrente_t_ultima' => $ultima->corrente_t ?? null,
                    'potencia_ativa_media' => $dado->pa_avg, 'potencia_ativa_max' => $dado->pa_max, 'potencia_ativa_min' => $dado->pa_min, 'potencia_ativa_ultima' => $ultima->potencia_ativa ?? null,
                    'potencia_reativa_media' => $dado->pr_avg, 'potencia_reativa_max' => $dado->pr_max, 'potencia_reativa_min' => $dado->pr_min, 'potencia_reativa_ultima' => $ultima->potencia_reativa ?? null,
                    'fator_potencia_media' => $dado->fp_avg, 'fator_potencia_max' => $dado->fp_max, 'fator_potencia_min' => $dado->fp_min, 'fator_potencia_ultima' => $ultima->fator_potencia ?? null,
                ];
            });
        });
    }

    /**
     * Função CORE de otimização: Realiza UPSERT em lotes e gerencia dados existentes em memória
     */
    private function upsertLote(array $novosDados, string $periodo, callable $mapCallback): void
    {
        // Divide o array gigante em pedaços menores para não estourar memória/query limit
        $chunks = array_chunk($novosDados, self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $chavesBusca = [];
            foreach ($chunk as $row) {
                // Cria chaves compostas para busca rápida
                $chavesBusca[] = "{$row->id_cliente}-{$row->id_equipamento}-{$row->periodo_inicio}";
            }

            // 1. Busca dados JÁ existentes no banco para este lote (Eager Loading)
            // Isso evita fazer um SELECT dentro do loop para cada item
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

                // Cálculo da data fim via PHP (Muito mais rápido que DATE_ADD no SQL)
                $inicio = Carbon::parse($dado->periodo_inicio);
                $fim = $periodo === 'hora' ? $inicio->copy()->addHour() : $inicio->copy()->addDay();

                // Executa o callback para mapear as colunas específicas da tabela (corrente, temp, etc)
                $camposEspecificos = $mapCallback($dado, $registroExistente);

                // Monta o array base
                $linha = array_merge([
                    'id_cliente' => $dado->id_cliente,
                    'id_equipamento' => $dado->id_equipamento,
                    'periodo_inicio' => $dado->periodo_inicio,
                    'periodo_fim' => $fim->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ], $camposEspecificos);

                // Lógica de soma de contagem
                if ($registroExistente) {
                    $linha['registros_contagem'] = $registroExistente->registros_contagem + $dado->total;
                    // Mantém created_at antigo, atualiza o resto
                    $linha['created_at'] = $registroExistente->created_at;
                } else {
                    $linha['registros_contagem'] = $dado->total;
                    $linha['created_at'] = now()->toDateTimeString();
                }

                $upsertData[] = $linha;
            }

            // 2. Realiza UPSERT (Insert or Update) em lote
            // Requer Unique Key no banco: (id_cliente, id_equipamento, periodo_inicio)
            if (!empty($upsertData)) {
                DB::table('dados_agregados')->upsert(
                    $upsertData, 
                    ['id_cliente', 'id_equipamento', 'periodo_inicio'], // Colunas chave única
                    array_keys(reset($upsertData)) // Atualiza todas as colunas passadas
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
            // Atualiza apenas o que foi processado até o início do comando (evita race condition)
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
            // Delete direto é mais rápido que buscar e deletar
            // Adicionado LIMIT para não travar o banco se houver milhões de linhas
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