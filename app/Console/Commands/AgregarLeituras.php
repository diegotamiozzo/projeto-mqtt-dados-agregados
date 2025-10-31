<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgregarLeituras extends Command
{
    /**
     * Nome e assinatura do comando no Artisan.
     */
    protected $signature = 'leituras:agregar {--periodo=hora}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Agrega leituras de sensores (médias, mínimas, máximas e últimas) e atualiza dados existentes.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $periodo = $this->option('periodo');
        $this->info("Iniciando agregação por {$periodo}...");

        // Define formato de agrupamento e intervalo
        switch ($periodo) {
            case 'hora':
                $format = '%Y-%m-%d %H:00:00';
                $intervalo = 'HOUR';
                break;
            case 'dia':
                $format = '%Y-%m-%d 00:00:00';
                $intervalo = 'DAY';
                break;
            default:
                $this->error('Período inválido. Use: hora ou dia.');
                return;
        }

        // SQL de agregação com atualização de registros existentes
        $sql = "
            INSERT INTO sensor_leituras_agregado (
                periodo_inicio,
                periodo_fim,
                temperatura_interna_media,
                temperatura_interna_max,
                temperatura_interna_min,
                temperatura_interna_ultima,
                temperatura_externa_media,
                temperatura_externa_max,
                temperatura_externa_min,
                temperatura_externa_ultima,
                umidade_interna_media,
                umidade_interna_max,
                umidade_interna_min,
                umidade_interna_ultima,
                umidade_externa_media,
                umidade_externa_max,
                umidade_externa_min,
                umidade_externa_ultima,
                peso_sensor_1_medio,
                peso_sensor_1_ultimo,
                peso_sensor_2_medio,
                peso_sensor_2_ultimo,
                luminosidade_media,
                luminosidade_ultima,
                registros_contagem,
                created_at,
                updated_at
            )
            SELECT
                t.periodo_inicio,
                DATE_ADD(t.periodo_inicio, INTERVAL 1 {$intervalo}) AS periodo_fim,
                AVG(t.temperatura_interna),
                MAX(t.temperatura_interna),
                MIN(t.temperatura_interna),
                MAX(CASE WHEN t.rn = 1 THEN t.temperatura_interna END),
                AVG(t.temperatura_externa),
                MAX(t.temperatura_externa),
                MIN(t.temperatura_externa),
                MAX(CASE WHEN t.rn = 1 THEN t.temperatura_externa END),
                AVG(t.umidade_interna),
                MAX(t.umidade_interna),
                MIN(t.umidade_interna),
                MAX(CASE WHEN t.rn = 1 THEN t.umidade_interna END),
                AVG(t.umidade_externa),
                MAX(t.umidade_externa),
                MIN(t.umidade_externa),
                MAX(CASE WHEN t.rn = 1 THEN t.umidade_externa END),
                AVG(t.peso_sensor_1),
                MAX(CASE WHEN t.rn = 1 THEN t.peso_sensor_1 END),
                AVG(t.peso_sensor_2),
                MAX(CASE WHEN t.rn = 1 THEN t.peso_sensor_2 END),
                AVG(t.luminosidade),
                MAX(CASE WHEN t.rn = 1 THEN t.luminosidade END),
                COUNT(*),
                NOW(),
                NOW()
            FROM (
                SELECT
                    *,
                    DATE_FORMAT(data_hora, '{$format}') AS periodo_inicio,
                    ROW_NUMBER() OVER (
                        PARTITION BY DATE_FORMAT(data_hora, '{$format}')
                        ORDER BY data_hora DESC
                    ) AS rn
                FROM sensor_leituras
                WHERE agregado = 0
            ) AS t
            GROUP BY t.periodo_inicio
            ON DUPLICATE KEY UPDATE
                temperatura_interna_media = VALUES(temperatura_interna_media),
                temperatura_interna_max = VALUES(temperatura_interna_max),
                temperatura_interna_min = VALUES(temperatura_interna_min),
                temperatura_interna_ultima = VALUES(temperatura_interna_ultima),
                temperatura_externa_media = VALUES(temperatura_externa_media),
                temperatura_externa_max = VALUES(temperatura_externa_max),
                temperatura_externa_min = VALUES(temperatura_externa_min),
                temperatura_externa_ultima = VALUES(temperatura_externa_ultima),
                umidade_interna_media = VALUES(umidade_interna_media),
                umidade_interna_max = VALUES(umidade_interna_max),
                umidade_interna_min = VALUES(umidade_interna_min),
                umidade_interna_ultima = VALUES(umidade_interna_ultima),
                umidade_externa_media = VALUES(umidade_externa_media),
                umidade_externa_max = VALUES(umidade_externa_max),
                umidade_externa_min = VALUES(umidade_externa_min),
                umidade_externa_ultima = VALUES(umidade_externa_ultima),
                peso_sensor_1_medio = VALUES(peso_sensor_1_medio),
                peso_sensor_1_ultimo = VALUES(peso_sensor_1_ultimo),
                peso_sensor_2_medio = VALUES(peso_sensor_2_medio),
                peso_sensor_2_ultimo = VALUES(peso_sensor_2_ultimo),
                luminosidade_media = VALUES(luminosidade_media),
                luminosidade_ultima = VALUES(luminosidade_ultima),
                registros_contagem = registros_contagem + VALUES(registros_contagem),
                updated_at = NOW();
        ";

        // Executa agregação
        try {
            DB::statement($sql);
            $this->info('Agregação concluída ou atualizada com sucesso.');
        } catch (\Exception $e) {
            $this->error("Erro durante a agregação: " . $e->getMessage());
            return;
        }

        // Marca registros como agregados
        try {
            $marcado = DB::table('sensor_leituras')->where('agregado', 0)->update(['agregado' => 1]);
            $this->info("Registros marcados como agregados: {$marcado}");
        } catch (\Exception $e) {
            $this->error(" Erro ao marcar registros: " . $e->getMessage());
        }

        // Limpa registros antigos
        try {
            $limite = Carbon::now()->subDays(30);
            $deleted = DB::table('sensor_leituras')
                ->where('data_hora', '<', $limite)
                ->delete();
            $this->info("Registros antigos apagados (anteriores a {$limite->toDateString()}): {$deleted}");
        } catch (\Exception $e) {
            $this->error("Erro ao limpar registros antigos: " . $e->getMessage());
        }

        $this->info(' Processo de agregação finalizado.');
    }
}
