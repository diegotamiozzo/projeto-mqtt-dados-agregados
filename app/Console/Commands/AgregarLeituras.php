<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgregarLeituras extends Command
{
    protected $signature = 'leituras:agregar {--periodo=hora : Período de agregação (hora ou dia)}';
    protected $description = 'Agrega leituras de equipamentos em dados_agregados com suporte a transações';

    public function handle()
    {
        $periodo = $this->option('periodo');
        $this->info("=== Iniciando agregação por {$periodo} ===");

        // Valida o período
        if (!in_array($periodo, ['hora', 'dia'])) {
            $this->error('Período inválido. Use: hora ou dia');
            return 1;
        }

        // Define formato e intervalo baseado no período
        [$format, $intervalo] = $this->getFormatoIntervalo($periodo);

        // Desabilita ONLY_FULL_GROUP_BY para esta sessão
        $this->ajustarSqlMode();

        // Inicia transação
        DB::beginTransaction();

        try {
            // 1. Agregar correntes (brunidores, descascadores, polidores)
            $this->agregarCorrentes($format, $intervalo);

            // 2. Agregar temperatura
            $this->agregarTemperatura($format, $intervalo);

            // 3. Agregar umidade
            $this->agregarUmidade($format, $intervalo);

            // 4. Agregar grandezas elétricas
            $this->agregarGrandezasEletricas($format, $intervalo);

            // 5. Marcar registros como agregados
            $this->marcarComoAgregado();

            // 6. Limpar registros antigos (> 30 dias)
            $this->limparRegistrosAntigos();

            // Commit da transação
            DB::commit();
            $this->info("✓ Agregação finalizada com sucesso!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Erro durante agregação: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Retorna formato e intervalo baseado no período
     */
    private function getFormatoIntervalo($periodo): array
    {
        return match($periodo) {
            'hora' => ['%Y-%m-%d %H:00:00', 'HOUR'],
            'dia' => ['%Y-%m-%d 00:00:00', 'DAY'],
        };
    }

    /**
     * Ajusta SQL Mode removendo ONLY_FULL_GROUP_BY
     */
    private function ajustarSqlMode(): void
    {
        try {
            DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
            $this->info("→ SQL Mode ajustado");
        } catch (\Exception $e) {
            $this->warn("Aviso ao ajustar SQL Mode: " . $e->getMessage());
        }
    }

    /**
     * Agrega dados de correntes (brunidores, descascadores, polidores)
     */
    private function agregarCorrentes(string $format, string $intervalo): void
    {
        $this->info("\n[1/5] Agregando correntes...");

        $sql = "
            INSERT INTO dados_agregados (
                id_cliente, id_equipamento, periodo_inicio, periodo_fim, registros_contagem,
                corrente_brunidores_media, corrente_brunidores_max, corrente_brunidores_min, corrente_brunidores_ultima,
                corrente_descascadores_media, corrente_descascadores_max, corrente_descascadores_min, corrente_descascadores_ultima,
                corrente_polidores_media, corrente_polidores_max, corrente_polidores_min, corrente_polidores_ultima,
                created_at, updated_at
            )
            SELECT
                base.id_cliente,
                base.id_equipamento,
                base.periodo_inicio,
                base.periodo_fim,
                base.total_registros,
                
                -- Brunidores
                base.corrente_brunidores_media,
                base.corrente_brunidores_max,
                base.corrente_brunidores_min,
                base.corrente_brunidores_ultima,
                
                -- Descascadores
                base.corrente_descascadores_media,
                base.corrente_descascadores_max,
                base.corrente_descascadores_min,
                base.corrente_descascadores_ultima,
                
                -- Polidores
                base.corrente_polidores_media,
                base.corrente_polidores_max,
                base.corrente_polidores_min,
                base.corrente_polidores_ultima,
                
                NOW() AS created_at,
                NOW() AS updated_at
            FROM (
                SELECT 
                    t.id_cliente,
                    t.id_equipamento,
                    DATE_FORMAT(t.timestamp, '{$format}') AS periodo_inicio,
                    DATE_ADD(DATE_FORMAT(t.timestamp, '{$format}'), INTERVAL 1 {$intervalo}) AS periodo_fim,
                    COUNT(DISTINCT t.id) AS total_registros,
                    
                    -- Brunidores: média, max, min, última
                    AVG(CASE WHEN t.tipo = 'brunidores' THEN t.corrente END) AS corrente_brunidores_media,
                    MAX(CASE WHEN t.tipo = 'brunidores' THEN t.corrente END) AS corrente_brunidores_max,
                    MIN(CASE WHEN t.tipo = 'brunidores' THEN t.corrente END) AS corrente_brunidores_min,
                    (SELECT cb.corrente FROM corrente_brunidores cb 
                     WHERE cb.id_cliente = t.id_cliente 
                       AND cb.id_equipamento = t.id_equipamento 
                       AND cb.agregado = 0
                       AND DATE_FORMAT(cb.timestamp, '{$format}') = DATE_FORMAT(t.timestamp, '{$format}')
                     ORDER BY cb.timestamp DESC LIMIT 1) AS corrente_brunidores_ultima,
                    
                    -- Descascadores: média, max, min, última
                    AVG(CASE WHEN t.tipo = 'descascadores' THEN t.corrente END) AS corrente_descascadores_media,
                    MAX(CASE WHEN t.tipo = 'descascadores' THEN t.corrente END) AS corrente_descascadores_max,
                    MIN(CASE WHEN t.tipo = 'descascadores' THEN t.corrente END) AS corrente_descascadores_min,
                    (SELECT cd.corrente FROM corrente_descascadores cd 
                     WHERE cd.id_cliente = t.id_cliente 
                       AND cd.id_equipamento = t.id_equipamento 
                       AND cd.agregado = 0
                       AND DATE_FORMAT(cd.timestamp, '{$format}') = DATE_FORMAT(t.timestamp, '{$format}')
                     ORDER BY cd.timestamp DESC LIMIT 1) AS corrente_descascadores_ultima,
                    
                    -- Polidores: média, max, min, última
                    AVG(CASE WHEN t.tipo = 'polidores' THEN t.corrente END) AS corrente_polidores_media,
                    MAX(CASE WHEN t.tipo = 'polidores' THEN t.corrente END) AS corrente_polidores_max,
                    MIN(CASE WHEN t.tipo = 'polidores' THEN t.corrente END) AS corrente_polidores_min,
                    (SELECT cp.corrente FROM corrente_polidores cp 
                     WHERE cp.id_cliente = t.id_cliente 
                       AND cp.id_equipamento = t.id_equipamento 
                       AND cp.agregado = 0
                       AND DATE_FORMAT(cp.timestamp, '{$format}') = DATE_FORMAT(t.timestamp, '{$format}')
                     ORDER BY cp.timestamp DESC LIMIT 1) AS corrente_polidores_ultima
                    
                FROM (
                    SELECT id, id_cliente, id_equipamento, timestamp, corrente, 'brunidores' AS tipo 
                    FROM corrente_brunidores WHERE agregado = 0
                    UNION ALL
                    SELECT id, id_cliente, id_equipamento, timestamp, corrente, 'descascadores' AS tipo 
                    FROM corrente_descascadores WHERE agregado = 0
                    UNION ALL
                    SELECT id, id_cliente, id_equipamento, timestamp, corrente, 'polidores' AS tipo 
                    FROM corrente_polidores WHERE agregado = 0
                ) AS t
                GROUP BY t.id_cliente, t.id_equipamento, periodo_inicio
            ) AS base
            ON DUPLICATE KEY UPDATE
                registros_contagem = dados_agregados.registros_contagem + VALUES(registros_contagem),
                corrente_brunidores_media = COALESCE(VALUES(corrente_brunidores_media), dados_agregados.corrente_brunidores_media),
                corrente_brunidores_max = GREATEST(COALESCE(dados_agregados.corrente_brunidores_max, 0), COALESCE(VALUES(corrente_brunidores_max), 0)),
                corrente_brunidores_min = LEAST(COALESCE(dados_agregados.corrente_brunidores_min, 999999), COALESCE(VALUES(corrente_brunidores_min), 999999)),
                corrente_brunidores_ultima = COALESCE(VALUES(corrente_brunidores_ultima), dados_agregados.corrente_brunidores_ultima),
                corrente_descascadores_media = COALESCE(VALUES(corrente_descascadores_media), dados_agregados.corrente_descascadores_media),
                corrente_descascadores_max = GREATEST(COALESCE(dados_agregados.corrente_descascadores_max, 0), COALESCE(VALUES(corrente_descascadores_max), 0)),
                corrente_descascadores_min = LEAST(COALESCE(dados_agregados.corrente_descascadores_min, 999999), COALESCE(VALUES(corrente_descascadores_min), 999999)),
                corrente_descascadores_ultima = COALESCE(VALUES(corrente_descascadores_ultima), dados_agregados.corrente_descascadores_ultima),
                corrente_polidores_media = COALESCE(VALUES(corrente_polidores_media), dados_agregados.corrente_polidores_media),
                corrente_polidores_max = GREATEST(COALESCE(dados_agregados.corrente_polidores_max, 0), COALESCE(VALUES(corrente_polidores_max), 0)),
                corrente_polidores_min = LEAST(COALESCE(dados_agregados.corrente_polidores_min, 999999), COALESCE(VALUES(corrente_polidores_min), 999999)),
                corrente_polidores_ultima = COALESCE(VALUES(corrente_polidores_ultima), dados_agregados.corrente_polidores_ultima),
                updated_at = NOW()
        ";

        DB::statement($sql);
        $this->info("  ✓ Correntes agregadas (média, max, min, última)");
    }

    /**
     * Agrega dados de temperatura
     */
    private function agregarTemperatura(string $format, string $intervalo): void
    {
        $this->info("\n[2/5] Agregando temperatura...");

        $sql = "
            INSERT INTO dados_agregados (
                id_cliente, id_equipamento, periodo_inicio, periodo_fim,
                temperatura_media, temperatura_max, temperatura_min, temperatura_ultima,
                registros_contagem,
                created_at, updated_at
            )
            SELECT
                id_cliente,
                id_equipamento,
                DATE_FORMAT(timestamp, '{$format}') AS periodo_inicio,
                DATE_ADD(DATE_FORMAT(timestamp, '{$format}'), INTERVAL 1 {$intervalo}) AS periodo_fim,
                AVG(temperatura) AS temperatura_media,
                MAX(temperatura) AS temperatura_max,
                MIN(temperatura) AS temperatura_min,
                (SELECT temperatura FROM temperaturas t2 
                 WHERE t2.id_cliente = t.id_cliente 
                   AND t2.id_equipamento = t.id_equipamento
                   AND t2.agregado = 0
                   AND DATE_FORMAT(t2.timestamp, '{$format}') = DATE_FORMAT(t.timestamp, '{$format}')
                 ORDER BY t2.timestamp DESC LIMIT 1) AS temperatura_ultima,
                COUNT(*) AS registros_contagem,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM temperaturas t
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
            ON DUPLICATE KEY UPDATE
                temperatura_media = COALESCE(VALUES(temperatura_media), dados_agregados.temperatura_media),
                temperatura_max = GREATEST(COALESCE(dados_agregados.temperatura_max, -999), COALESCE(VALUES(temperatura_max), -999)),
                temperatura_min = LEAST(COALESCE(dados_agregados.temperatura_min, 999), COALESCE(VALUES(temperatura_min), 999)),
                temperatura_ultima = COALESCE(VALUES(temperatura_ultima), dados_agregados.temperatura_ultima),
                updated_at = NOW()
        ";

        DB::statement($sql);
        $this->info("  ✓ Temperatura agregada (média, max, min, última)");
    }

    /**
     * Agrega dados de umidade
     */
    private function agregarUmidade(string $format, string $intervalo): void
    {
        $this->info("\n[3/5] Agregando umidade...");

        $sql = "
            INSERT INTO dados_agregados (
                id_cliente, id_equipamento, periodo_inicio, periodo_fim,
                umidade_media, umidade_max, umidade_min, umidade_ultima,
                registros_contagem,
                created_at, updated_at
            )
            SELECT
                id_cliente,
                id_equipamento,
                DATE_FORMAT(timestamp, '{$format}') AS periodo_inicio,
                DATE_ADD(DATE_FORMAT(timestamp, '{$format}'), INTERVAL 1 {$intervalo}) AS periodo_fim,
                AVG(umidade) AS umidade_media,
                MAX(umidade) AS umidade_max,
                MIN(umidade) AS umidade_min,
                (SELECT umidade FROM umidades u2 
                 WHERE u2.id_cliente = u.id_cliente 
                   AND u2.id_equipamento = u.id_equipamento
                   AND u2.agregado = 0
                   AND DATE_FORMAT(u2.timestamp, '{$format}') = DATE_FORMAT(u.timestamp, '{$format}')
                 ORDER BY u2.timestamp DESC LIMIT 1) AS umidade_ultima,
                COUNT(*) AS registros_contagem,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM umidades u
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
            ON DUPLICATE KEY UPDATE
                umidade_media = COALESCE(VALUES(umidade_media), dados_agregados.umidade_media),
                umidade_max = GREATEST(COALESCE(dados_agregados.umidade_max, 0), COALESCE(VALUES(umidade_max), 0)),
                umidade_min = LEAST(COALESCE(dados_agregados.umidade_min, 100), COALESCE(VALUES(umidade_min), 100)),
                umidade_ultima = COALESCE(VALUES(umidade_ultima), dados_agregados.umidade_ultima),
                updated_at = NOW()
        ";

        DB::statement($sql);
        $this->info("  ✓ Umidade agregada (média, max, min, última)");
    }

    /**
     * Agrega grandezas elétricas
     */
    private function agregarGrandezasEletricas(string $format, string $intervalo): void
    {
        $this->info("\n[4/5] Agregando grandezas elétricas...");

        $sql = "
            INSERT INTO dados_agregados (
                id_cliente, id_equipamento, periodo_inicio, periodo_fim,
                tensao_r_media, tensao_r_max, tensao_r_min, tensao_r_ultima,
                tensao_s_media, tensao_s_max, tensao_s_min, tensao_s_ultima,
                tensao_t_media, tensao_t_max, tensao_t_min, tensao_t_ultima,
                corrente_r_media, corrente_r_max, corrente_r_min, corrente_r_ultima,
                corrente_s_media, corrente_s_max, corrente_s_min, corrente_s_ultima,
                corrente_t_media, corrente_t_max, corrente_t_min, corrente_t_ultima,
                potencia_ativa_media, potencia_ativa_max, potencia_ativa_min, potencia_ativa_ultima,
                potencia_reativa_media, potencia_reativa_max, potencia_reativa_min, potencia_reativa_ultima,
                fator_potencia_media, fator_potencia_max, fator_potencia_min, fator_potencia_ultima,
                registros_contagem,
                created_at, updated_at
            )
            SELECT
                id_cliente,
                id_equipamento,
                DATE_FORMAT(timestamp, '{$format}') AS periodo_inicio,
                DATE_ADD(DATE_FORMAT(timestamp, '{$format}'), INTERVAL 1 {$intervalo}) AS periodo_fim,
                
                -- Tensões R, S, T
                AVG(tensao_r) AS tensao_r_media, 
                MAX(tensao_r) AS tensao_r_max, 
                MIN(tensao_r) AS tensao_r_min,
                (SELECT tensao_r FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS tensao_r_ultima,
                
                AVG(tensao_s) AS tensao_s_media, 
                MAX(tensao_s) AS tensao_s_max, 
                MIN(tensao_s) AS tensao_s_min,
                (SELECT tensao_s FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS tensao_s_ultima,
                
                AVG(tensao_t) AS tensao_t_media, 
                MAX(tensao_t) AS tensao_t_max, 
                MIN(tensao_t) AS tensao_t_min,
                (SELECT tensao_t FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS tensao_t_ultima,
                
                -- Correntes R, S, T
                AVG(corrente_r) AS corrente_r_media, 
                MAX(corrente_r) AS corrente_r_max, 
                MIN(corrente_r) AS corrente_r_min,
                (SELECT corrente_r FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS corrente_r_ultima,
                
                AVG(corrente_s) AS corrente_s_media, 
                MAX(corrente_s) AS corrente_s_max, 
                MIN(corrente_s) AS corrente_s_min,
                (SELECT corrente_s FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS corrente_s_ultima,
                
                AVG(corrente_t) AS corrente_t_media, 
                MAX(corrente_t) AS corrente_t_max, 
                MIN(corrente_t) AS corrente_t_min,
                (SELECT corrente_t FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS corrente_t_ultima,
                
                -- Potências
                AVG(potencia_ativa) AS potencia_ativa_media, 
                MAX(potencia_ativa) AS potencia_ativa_max, 
                MIN(potencia_ativa) AS potencia_ativa_min,
                (SELECT potencia_ativa FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS potencia_ativa_ultima,
                
                AVG(potencia_reativa) AS potencia_reativa_media, 
                MAX(potencia_reativa) AS potencia_reativa_max, 
                MIN(potencia_reativa) AS potencia_reativa_min,
                (SELECT potencia_reativa FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS potencia_reativa_ultima,
                
                -- Fator de Potência
                AVG(fator_potencia) AS fator_potencia_media, 
                MAX(fator_potencia) AS fator_potencia_max, 
                MIN(fator_potencia) AS fator_potencia_min,
                (SELECT fator_potencia FROM grandezas_eletricas g2 
                 WHERE g2.id_cliente = g.id_cliente AND g2.id_equipamento = g.id_equipamento
                   AND g2.agregado = 0 AND DATE_FORMAT(g2.timestamp, '{$format}') = DATE_FORMAT(g.timestamp, '{$format}')
                 ORDER BY g2.timestamp DESC LIMIT 1) AS fator_potencia_ultima,
                
                COUNT(*) AS registros_contagem,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM grandezas_eletricas g
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
            ON DUPLICATE KEY UPDATE
                tensao_r_media = COALESCE(VALUES(tensao_r_media), dados_agregados.tensao_r_media),
                tensao_r_max = GREATEST(COALESCE(dados_agregados.tensao_r_max, 0), COALESCE(VALUES(tensao_r_max), 0)),
                tensao_r_min = LEAST(COALESCE(dados_agregados.tensao_r_min, 999999), COALESCE(VALUES(tensao_r_min), 999999)),
                tensao_r_ultima = COALESCE(VALUES(tensao_r_ultima), dados_agregados.tensao_r_ultima),
                
                tensao_s_media = COALESCE(VALUES(tensao_s_media), dados_agregados.tensao_s_media),
                tensao_s_max = GREATEST(COALESCE(dados_agregados.tensao_s_max, 0), COALESCE(VALUES(tensao_s_max), 0)),
                tensao_s_min = LEAST(COALESCE(dados_agregados.tensao_s_min, 999999), COALESCE(VALUES(tensao_s_min), 999999)),
                tensao_s_ultima = COALESCE(VALUES(tensao_s_ultima), dados_agregados.tensao_s_ultima),
                
                tensao_t_media = COALESCE(VALUES(tensao_t_media), dados_agregados.tensao_t_media),
                tensao_t_max = GREATEST(COALESCE(dados_agregados.tensao_t_max, 0), COALESCE(VALUES(tensao_t_max), 0)),
                tensao_t_min = LEAST(COALESCE(dados_agregados.tensao_t_min, 999999), COALESCE(VALUES(tensao_t_min), 999999)),
                tensao_t_ultima = COALESCE(VALUES(tensao_t_ultima), dados_agregados.tensao_t_ultima),
                
                corrente_r_media = COALESCE(VALUES(corrente_r_media), dados_agregados.corrente_r_media),
                corrente_r_max = GREATEST(COALESCE(dados_agregados.corrente_r_max, 0), COALESCE(VALUES(corrente_r_max), 0)),
                corrente_r_min = LEAST(COALESCE(dados_agregados.corrente_r_min, 999999), COALESCE(VALUES(corrente_r_min), 999999)),
                corrente_r_ultima = COALESCE(VALUES(corrente_r_ultima), dados_agregados.corrente_r_ultima),
                
                corrente_s_media = COALESCE(VALUES(corrente_s_media), dados_agregados.corrente_s_media),
                corrente_s_max = GREATEST(COALESCE(dados_agregados.corrente_s_max, 0), COALESCE(VALUES(corrente_s_max), 0)),
                corrente_s_min = LEAST(COALESCE(dados_agregados.corrente_s_min, 999999), COALESCE(VALUES(corrente_s_min), 999999)),
                corrente_s_ultima = COALESCE(VALUES(corrente_s_ultima), dados_agregados.corrente_s_ultima),
                
                corrente_t_media = COALESCE(VALUES(corrente_t_media), dados_agregados.corrente_t_media),
                corrente_t_max = GREATEST(COALESCE(dados_agregados.corrente_t_max, 0), COALESCE(VALUES(corrente_t_max), 0)),
                corrente_t_min = LEAST(COALESCE(dados_agregados.corrente_t_min, 999999), COALESCE(VALUES(corrente_t_min), 999999)),
                corrente_t_ultima = COALESCE(VALUES(corrente_t_ultima), dados_agregados.corrente_t_ultima),
                
                potencia_ativa_media = COALESCE(VALUES(potencia_ativa_media), dados_agregados.potencia_ativa_media),
                potencia_ativa_max = GREATEST(COALESCE(dados_agregados.potencia_ativa_max, 0), COALESCE(VALUES(potencia_ativa_max), 0)),
                potencia_ativa_min = LEAST(COALESCE(dados_agregados.potencia_ativa_min, 999999), COALESCE(VALUES(potencia_ativa_min), 999999)),
                potencia_ativa_ultima = COALESCE(VALUES(potencia_ativa_ultima), dados_agregados.potencia_ativa_ultima),
                
                potencia_reativa_media = COALESCE(VALUES(potencia_reativa_media), dados_agregados.potencia_reativa_media),
                potencia_reativa_max = GREATEST(COALESCE(dados_agregados.potencia_reativa_max, 0), COALESCE(VALUES(potencia_reativa_max), 0)),
                potencia_reativa_min = LEAST(COALESCE(dados_agregados.potencia_reativa_min, 999999), COALESCE(VALUES(potencia_reativa_min), 999999)),
                potencia_reativa_ultima = COALESCE(VALUES(potencia_reativa_ultima), dados_agregados.potencia_reativa_ultima),
                
                fator_potencia_media = COALESCE(VALUES(fator_potencia_media), dados_agregados.fator_potencia_media),
                fator_potencia_max = GREATEST(COALESCE(dados_agregados.fator_potencia_max, 0), COALESCE(VALUES(fator_potencia_max), 0)),
                fator_potencia_min = LEAST(COALESCE(dados_agregados.fator_potencia_min, 2), COALESCE(VALUES(fator_potencia_min), 2)),
                fator_potencia_ultima = COALESCE(VALUES(fator_potencia_ultima), dados_agregados.fator_potencia_ultima),
                
                updated_at = NOW()
        ";

        DB::statement($sql);
        $this->info("  ✓ Grandezas elétricas agregadas (média, max, min, última)");
    }

    /**
     * Marca todos os registros como agregados
     */
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
            $this->info("  → {$tabela}: {$count} registros");
            $totalMarcado += $count;
        }

        $this->info("  ✓ Total marcado: {$totalMarcado} registros");
    }

    /**
     * Remove registros com mais de 30 dias
     */
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
                ->delete();
            
            if ($count > 0) {
                $this->info("  → {$tabela}: {$count} registros removidos");
            }
            $totalApagado += $count;
        }

        $this->info("  ✓ Total removido: {$totalApagado} registros");
    }
}