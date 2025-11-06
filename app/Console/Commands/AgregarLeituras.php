<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgregarLeituras extends Command
{
    protected $signature = 'leituras:agregar {--periodo=hora : Período de agregação (hora ou dia)}';
    protected $description = 'Agrega leituras de equipamentos em dados_agregados';

    public function handle()
    {
        $periodo = $this->option('periodo');
        $this->info("=== Iniciando agregação por {$periodo} ===");

        if (!in_array($periodo, ['hora', 'dia'])) {
            $this->error('Período inválido. Use: hora ou dia');
            return 1;
        }

        DB::beginTransaction();

        try {
            $this->agregarCorrentes($periodo);
            $this->agregarTemperatura($periodo);
            $this->agregarUmidade($periodo);
            $this->agregarGrandezasEletricas($periodo);
            $this->marcarComoAgregado();
            $this->limparRegistrosAntigos();

            DB::commit();
            $this->info("✓ Agregação finalizada com sucesso!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Erro durante agregação: " . $e->getMessage());
            return 1;
        }
    }

    private function getDriver(): string
    {
        return DB::connection()->getDriverName();
    }

    private function dateFormat($campo, $periodo): string
    {
        $driver = $this->getDriver();

        if ($driver === 'mysql') {
            $formato = match($periodo) {
                'hora' => '%Y-%m-%d %H:00:00',
                'dia' => '%Y-%m-%d 00:00:00',
            };
            return "DATE_FORMAT({$campo}, '{$formato}')";
        } else {
            $formato = match($periodo) {
                'hora' => 'YYYY-MM-DD HH24:00:00',
                'dia' => 'YYYY-MM-DD 00:00:00',
            };
            return "TO_CHAR({$campo}, '{$formato}')";
        }
    }

    private function dateAdd($campo, $periodo): string
    {
        $driver = $this->getDriver();
        $intervalo = match($periodo) {
            'hora' => '1 HOUR',
            'dia' => '1 DAY',
        };

        if ($driver === 'mysql') {
            return "DATE_ADD({$campo}, INTERVAL {$intervalo})";
        } else {
            return "({$campo} + INTERVAL '{$intervalo}')";
        }
    }

    private function agregarCorrentes(string $periodo): void
    {
        $this->info("\n[1/5] Agregando correntes...");

        $dateFormatExpr = $this->dateFormat('timestamp', $periodo);

        $tiposCorrentes = [
            ['tabela' => 'corrente_brunidores', 'prefixo' => 'brunidores'],
            ['tabela' => 'corrente_descascadores', 'prefixo' => 'descascadores'],
            ['tabela' => 'corrente_polidores', 'prefixo' => 'polidores']
        ];

        foreach ($tiposCorrentes as $tipo) {
            $tabela = $tipo['tabela'];
            $prefixo = $tipo['prefixo'];

            $dados = DB::select("
                SELECT
                    id_cliente,
                    id_equipamento,
                    {$dateFormatExpr} AS periodo_inicio,
                    AVG(corrente) AS corrente_media,
                    MAX(corrente) AS corrente_max,
                    MIN(corrente) AS corrente_min,
                    COUNT(*) AS registros_contagem
                FROM {$tabela}
                WHERE agregado = 0
                GROUP BY id_cliente, id_equipamento, periodo_inicio
            ");

            foreach ($dados as $dado) {
                $ultimaLeitura = DB::selectOne("
                    SELECT corrente
                    FROM {$tabela}
                    WHERE id_cliente = ?
                      AND id_equipamento = ?
                      AND agregado = 0
                      AND {$dateFormatExpr} = ?
                    ORDER BY timestamp DESC
                    LIMIT 1
                ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

                $dateAddExpr = $this->dateAdd('?', $periodo);
                $periodoFim = DB::selectOne("
                    SELECT {$dateAddExpr} AS periodo_fim
                ", [$dado->periodo_inicio])->periodo_fim;

                $existente = DB::table('dados_agregados')
                    ->where('id_cliente', $dado->id_cliente)
                    ->where('id_equipamento', $dado->id_equipamento)
                    ->where('periodo_inicio', $dado->periodo_inicio)
                    ->first();

                $dadosAgregar = [
                    'id_cliente' => $dado->id_cliente,
                    'id_equipamento' => $dado->id_equipamento,
                    'periodo_inicio' => $dado->periodo_inicio,
                    'periodo_fim' => $periodoFim,
                    "corrente_{$prefixo}_media" => $dado->corrente_media,
                    "corrente_{$prefixo}_max" => $dado->corrente_max,
                    "corrente_{$prefixo}_min" => $dado->corrente_min,
                    "corrente_{$prefixo}_ultima" => $ultimaLeitura ? $ultimaLeitura->corrente : null,
                    'updated_at' => now()
                ];

                if ($existente) {
                    $dadosAgregar['registros_contagem'] = $existente->registros_contagem + $dado->registros_contagem;

                    DB::table('dados_agregados')
                        ->where('id', $existente->id)
                        ->update($dadosAgregar);
                } else {
                    $dadosAgregar['registros_contagem'] = $dado->registros_contagem;
                    $dadosAgregar['created_at'] = now();

                    DB::table('dados_agregados')->insert($dadosAgregar);
                }
            }

            $this->info("  ✓ {$tabela} agregada");
        }
    }

    private function agregarTemperatura(string $periodo): void
    {
        $this->info("\n[2/5] Agregando temperatura...");

        $dateFormatExpr = $this->dateFormat('timestamp', $periodo);

        $dados = DB::select("
            SELECT
                id_cliente,
                id_equipamento,
                {$dateFormatExpr} AS periodo_inicio,
                AVG(temperatura) AS temperatura_media,
                MAX(temperatura) AS temperatura_max,
                MIN(temperatura) AS temperatura_min,
                COUNT(*) AS registros_contagem
            FROM temperaturas
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        foreach ($dados as $dado) {
            $ultimaLeitura = DB::selectOne("
                SELECT temperatura
                FROM temperaturas
                WHERE id_cliente = ?
                  AND id_equipamento = ?
                  AND agregado = 0
                  AND {$dateFormatExpr} = ?
                ORDER BY timestamp DESC
                LIMIT 1
            ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

            $dateAddExpr = $this->dateAdd('?', $periodo);
            $periodoFim = DB::selectOne("
                SELECT {$dateAddExpr} AS periodo_fim
            ", [$dado->periodo_inicio])->periodo_fim;

            $existente = DB::table('dados_agregados')
                ->where('id_cliente', $dado->id_cliente)
                ->where('id_equipamento', $dado->id_equipamento)
                ->where('periodo_inicio', $dado->periodo_inicio)
                ->first();

            $dadosAgregar = [
                'id_cliente' => $dado->id_cliente,
                'id_equipamento' => $dado->id_equipamento,
                'periodo_inicio' => $dado->periodo_inicio,
                'periodo_fim' => $periodoFim,
                'temperatura_media' => $dado->temperatura_media,
                'temperatura_max' => $dado->temperatura_max,
                'temperatura_min' => $dado->temperatura_min,
                'temperatura_ultima' => $ultimaLeitura ? $ultimaLeitura->temperatura : null,
                'updated_at' => now()
            ];

            if ($existente) {
                $dadosAgregar['registros_contagem'] = $existente->registros_contagem + $dado->registros_contagem;

                DB::table('dados_agregados')
                    ->where('id', $existente->id)
                    ->update($dadosAgregar);
            } else {
                $dadosAgregar['registros_contagem'] = $dado->registros_contagem;
                $dadosAgregar['created_at'] = now();

                DB::table('dados_agregados')->insert($dadosAgregar);
            }
        }

        $this->info("  ✓ Temperatura agregada");
    }

    private function agregarUmidade(string $periodo): void
    {
        $this->info("\n[3/5] Agregando umidade...");

        $dateFormatExpr = $this->dateFormat('timestamp', $periodo);

        $dados = DB::select("
            SELECT
                id_cliente,
                id_equipamento,
                {$dateFormatExpr} AS periodo_inicio,
                AVG(umidade) AS umidade_media,
                MAX(umidade) AS umidade_max,
                MIN(umidade) AS umidade_min,
                COUNT(*) AS registros_contagem
            FROM umidades
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        foreach ($dados as $dado) {
            $ultimaLeitura = DB::selectOne("
                SELECT umidade
                FROM umidades
                WHERE id_cliente = ?
                  AND id_equipamento = ?
                  AND agregado = 0
                  AND {$dateFormatExpr} = ?
                ORDER BY timestamp DESC
                LIMIT 1
            ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

            $dateAddExpr = $this->dateAdd('?', $periodo);
            $periodoFim = DB::selectOne("
                SELECT {$dateAddExpr} AS periodo_fim
            ", [$dado->periodo_inicio])->periodo_fim;

            $existente = DB::table('dados_agregados')
                ->where('id_cliente', $dado->id_cliente)
                ->where('id_equipamento', $dado->id_equipamento)
                ->where('periodo_inicio', $dado->periodo_inicio)
                ->first();

            $dadosAgregar = [
                'id_cliente' => $dado->id_cliente,
                'id_equipamento' => $dado->id_equipamento,
                'periodo_inicio' => $dado->periodo_inicio,
                'periodo_fim' => $periodoFim,
                'umidade_media' => $dado->umidade_media,
                'umidade_max' => $dado->umidade_max,
                'umidade_min' => $dado->umidade_min,
                'umidade_ultima' => $ultimaLeitura ? $ultimaLeitura->umidade : null,
                'updated_at' => now()
            ];

            if ($existente) {
                $dadosAgregar['registros_contagem'] = $existente->registros_contagem + $dado->registros_contagem;

                DB::table('dados_agregados')
                    ->where('id', $existente->id)
                    ->update($dadosAgregar);
            } else {
                $dadosAgregar['registros_contagem'] = $dado->registros_contagem;
                $dadosAgregar['created_at'] = now();

                DB::table('dados_agregados')->insert($dadosAgregar);
            }
        }

        $this->info("  ✓ Umidade agregada");
    }

    private function agregarGrandezasEletricas(string $periodo): void
    {
        $this->info("\n[4/5] Agregando grandezas elétricas...");

        $dateFormatExpr = $this->dateFormat('timestamp', $periodo);

        $dados = DB::select("
            SELECT
                id_cliente,
                id_equipamento,
                {$dateFormatExpr} AS periodo_inicio,
                AVG(tensao_r) AS tensao_r_media,
                MAX(tensao_r) AS tensao_r_max,
                MIN(tensao_r) AS tensao_r_min,
                AVG(tensao_s) AS tensao_s_media,
                MAX(tensao_s) AS tensao_s_max,
                MIN(tensao_s) AS tensao_s_min,
                AVG(tensao_t) AS tensao_t_media,
                MAX(tensao_t) AS tensao_t_max,
                MIN(tensao_t) AS tensao_t_min,
                AVG(corrente_r) AS corrente_r_media,
                MAX(corrente_r) AS corrente_r_max,
                MIN(corrente_r) AS corrente_r_min,
                AVG(corrente_s) AS corrente_s_media,
                MAX(corrente_s) AS corrente_s_max,
                MIN(corrente_s) AS corrente_s_min,
                AVG(corrente_t) AS corrente_t_media,
                MAX(corrente_t) AS corrente_t_max,
                MIN(corrente_t) AS corrente_t_min,
                AVG(potencia_ativa) AS potencia_ativa_media,
                MAX(potencia_ativa) AS potencia_ativa_max,
                MIN(potencia_ativa) AS potencia_ativa_min,
                AVG(potencia_reativa) AS potencia_reativa_media,
                MAX(potencia_reativa) AS potencia_reativa_max,
                MIN(potencia_reativa) AS potencia_reativa_min,
                AVG(fator_potencia) AS fator_potencia_media,
                MAX(fator_potencia) AS fator_potencia_max,
                MIN(fator_potencia) AS fator_potencia_min,
                COUNT(*) AS registros_contagem
            FROM grandezas_eletricas
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        foreach ($dados as $dado) {
            $dateFormatExpr = $this->dateFormat('timestamp', $periodo);
            $ultimaLeitura = DB::selectOne("
                SELECT tensao_r, tensao_s, tensao_t,
                       corrente_r, corrente_s, corrente_t,
                       potencia_ativa, potencia_reativa, fator_potencia
                FROM grandezas_eletricas
                WHERE id_cliente = ?
                  AND id_equipamento = ?
                  AND agregado = 0
                  AND {$dateFormatExpr} = ?
                ORDER BY timestamp DESC
                LIMIT 1
            ", [$dado->id_cliente, $dado->id_equipamento, $dado->periodo_inicio]);

            $dateAddExpr = $this->dateAdd('?', $periodo);
            $periodoFim = DB::selectOne("
                SELECT {$dateAddExpr} AS periodo_fim
            ", [$dado->periodo_inicio])->periodo_fim;

            $existente = DB::table('dados_agregados')
                ->where('id_cliente', $dado->id_cliente)
                ->where('id_equipamento', $dado->id_equipamento)
                ->where('periodo_inicio', $dado->periodo_inicio)
                ->first();

            $dadosAgregar = [
                'id_cliente' => $dado->id_cliente,
                'id_equipamento' => $dado->id_equipamento,
                'periodo_inicio' => $dado->periodo_inicio,
                'periodo_fim' => $periodoFim,
                'tensao_r_media' => $dado->tensao_r_media,
                'tensao_r_max' => $dado->tensao_r_max,
                'tensao_r_min' => $dado->tensao_r_min,
                'tensao_r_ultima' => $ultimaLeitura ? $ultimaLeitura->tensao_r : null,
                'tensao_s_media' => $dado->tensao_s_media,
                'tensao_s_max' => $dado->tensao_s_max,
                'tensao_s_min' => $dado->tensao_s_min,
                'tensao_s_ultima' => $ultimaLeitura ? $ultimaLeitura->tensao_s : null,
                'tensao_t_media' => $dado->tensao_t_media,
                'tensao_t_max' => $dado->tensao_t_max,
                'tensao_t_min' => $dado->tensao_t_min,
                'tensao_t_ultima' => $ultimaLeitura ? $ultimaLeitura->tensao_t : null,
                'corrente_r_media' => $dado->corrente_r_media,
                'corrente_r_max' => $dado->corrente_r_max,
                'corrente_r_min' => $dado->corrente_r_min,
                'corrente_r_ultima' => $ultimaLeitura ? $ultimaLeitura->corrente_r : null,
                'corrente_s_media' => $dado->corrente_s_media,
                'corrente_s_max' => $dado->corrente_s_max,
                'corrente_s_min' => $dado->corrente_s_min,
                'corrente_s_ultima' => $ultimaLeitura ? $ultimaLeitura->corrente_s : null,
                'corrente_t_media' => $dado->corrente_t_media,
                'corrente_t_max' => $dado->corrente_t_max,
                'corrente_t_min' => $dado->corrente_t_min,
                'corrente_t_ultima' => $ultimaLeitura ? $ultimaLeitura->corrente_t : null,
                'potencia_ativa_media' => $dado->potencia_ativa_media,
                'potencia_ativa_max' => $dado->potencia_ativa_max,
                'potencia_ativa_min' => $dado->potencia_ativa_min,
                'potencia_ativa_ultima' => $ultimaLeitura ? $ultimaLeitura->potencia_ativa : null,
                'potencia_reativa_media' => $dado->potencia_reativa_media,
                'potencia_reativa_max' => $dado->potencia_reativa_max,
                'potencia_reativa_min' => $dado->potencia_reativa_min,
                'potencia_reativa_ultima' => $ultimaLeitura ? $ultimaLeitura->potencia_reativa : null,
                'fator_potencia_media' => $dado->fator_potencia_media,
                'fator_potencia_max' => $dado->fator_potencia_max,
                'fator_potencia_min' => $dado->fator_potencia_min,
                'fator_potencia_ultima' => $ultimaLeitura ? $ultimaLeitura->fator_potencia : null,
                'updated_at' => now()
            ];

            if ($existente) {
                $dadosAgregar['registros_contagem'] = $existente->registros_contagem + $dado->registros_contagem;

                DB::table('dados_agregados')
                    ->where('id', $existente->id)
                    ->update($dadosAgregar);
            } else {
                $dadosAgregar['registros_contagem'] = $dado->registros_contagem;
                $dadosAgregar['created_at'] = now();

                DB::table('dados_agregados')->insert($dadosAgregar);
            }
        }

        $this->info("  ✓ Grandezas elétricas agregadas");
    }

    private function marcarComoAgregado(): void
    {
        $this->info("\n[5/5] Marcando registros como agregados...");

        $tabelas = [
            'corrente_brunidores',
            'corrente_descascadores',
            'corrente_polidores',
            'temperaturas',
            'umidades',
            'grandezas_eletricas'
        ];

        $totalMarcado = 0;
        foreach ($tabelas as $tabela) {
            $count = DB::table($tabela)->where('agregado', 0)->update(['agregado' => 1]);
            if ($count > 0) {
                $this->info("  → {$tabela}: {$count} registros");
            }
            $totalMarcado += $count;
        }

        $this->info("  ✓ Total marcado: {$totalMarcado} registros");
    }

    private function limparRegistrosAntigos(): void
    {
        $this->info("\nLimpando registros antigos (> 30 dias)...");

        $limite = Carbon::now()->subDays(30);
        $tabelas = [
            'corrente_brunidores',
            'corrente_descascadores',
            'corrente_polidores',
            'temperaturas',
            'umidades',
            'grandezas_eletricas'
        ];

        $totalApagado = 0;
        foreach ($tabelas as $tabela) {
            $count = DB::table($tabela)
                ->where('timestamp', '<', $limite)
                ->where('agregado', 1)
                ->delete();

            if ($count > 0) {
                $this->info("  → {$tabela}: {$count} registros removidos");
            }
            $totalApagado += $count;
        }

        $this->info("  ✓ Total removido: {$totalApagado} registros");
    }
}
