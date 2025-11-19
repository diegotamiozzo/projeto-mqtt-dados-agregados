<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AgregarLeituras extends Command
{
    protected $signature = 'leituras:agregar {--periodo=hora}';
    protected $description = 'Agrega leituras em dados_agregados (corrente, temperatura, umidade e grandezas elétricas)';

    public function handle()
    {
        $periodo = $this->option('periodo');

        if (!in_array($periodo, ['hora', 'dia'])) {
            $this->error('Período inválido. Use: hora ou dia');
            return 1;
        }

        $this->info("== Agregação por {$periodo} ==");

        DB::beginTransaction();
        try {
            $this->processar();
            DB::commit();
            $this->info("✓ Processo concluído");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Erro: ".$e->getMessage());
            return 1;
        }

        return 0;
    }

    private function format($campo, $periodo)
    {
        return $periodo === 'hora'
            ? "DATE_FORMAT({$campo}, '%Y-%m-%d %H:00:00')"
            : "DATE_FORMAT({$campo}, '%Y-%m-%d 00:00:00')";
    }

    private function addPeriodo($periodo)
    {
        return $periodo === 'hora'
            ? "INTERVAL 1 HOUR"
            : "INTERVAL 1 DAY";
    }

    private function processar()
    {
        /** DEFINIÇÃO DO PIPELINE GENÉRICO */
        $fontes = [

            // Correntes
            ['tabela' => 'corrente_brunidores',  'campo' => 'corrente', 'prefixo' => 'corrente_brunidores'],
            ['tabela' => 'corrente_descascadores', 'campo' => 'corrente', 'prefixo' => 'corrente_descascadores'],
            ['tabela' => 'corrente_polidores',     'campo' => 'corrente', 'prefixo' => 'corrente_polidores'],

            // Temperatura
            ['tabela' => 'temperaturas', 'campo' => 'temperatura', 'prefixo' => 'temperatura'],

            // Umidade
            ['tabela' => 'umidades', 'campo' => 'umidade', 'prefixo' => 'umidade'],

            // Grandezas elétricas
            ['tabela' => 'grandezas_eletricas', 'campo' => 'tensao_r', 'prefixo' => 'grandezas_eletricas', 'tipo' => 'eletrica']
        ];

        foreach ($fontes as $fonte) {
            $this->agregarTabela($fonte);
        }

        $this->marcarAgregado();
        $this->limparAntigos();
    }

    private function agregarTabela($fonte)
    {
        $tabela = $fonte['tabela'];
        $campo = $fonte['campo'];
        $prefixo = $fonte['prefixo'];
        $tipo = $fonte['tipo'] ?? 'padrao';

        $this->info("→ Agregando {$tabela}");

        $periodo = $this->option('periodo');
        $fmt = $this->format('timestamp', $periodo);
        $add = $this->addPeriodo($periodo);

        // ----------------------------------------------------
        // Caso especial: grandezas elétricas tem vários campos
        // ----------------------------------------------------

        if ($tipo === 'eletrica') {

            $campos = [
                'tensao_r', 'tensao_s', 'tensao_t',
                'corrente_r', 'corrente_s', 'corrente_t',
                'potencia_ativa', 'potencia_reativa', 'fator_potencia'
            ];

            $selectFields = collect($campos)->map(fn($c)=>
                "AVG($c) AS {$c}_media, MAX($c) AS {$c}_max, MIN($c) AS {$c}_min"
            )->implode(",\n");

        } else {

            // Campos simples
            $selectFields = "AVG({$campo}) AS {$prefixo}_media,
                             MAX({$campo}) AS {$prefixo}_max,
                             MIN({$campo}) AS {$prefixo}_min";
        }

        // ---------------------------------------
        // Executa agregação GENÉRICA por período
        // ---------------------------------------

        $dados = DB::select("
            SELECT 
                id_cliente,
                id_equipamento,
                {$fmt} AS periodo_inicio,
                {$selectFields},
                COUNT(*) AS registros_contagem
            FROM {$tabela}
            WHERE agregado = 0
            GROUP BY id_cliente, id_equipamento, periodo_inicio
        ");

        foreach ($dados as $d) {

            // última leitura
            $ultima = DB::selectOne("
                SELECT *
                FROM {$tabela}
                WHERE id_cliente = ?
                AND id_equipamento = ?
                AND agregado = 0
                AND {$fmt} = ?
                ORDER BY timestamp DESC
                LIMIT 1
            ", [$d->id_cliente, $d->id_equipamento, $d->periodo_inicio]);

            $periodoFim = DB::selectOne("SELECT ( ? + {$add} ) AS fim", [$d->periodo_inicio])->fim;

            // existe registro agregado?
            $exist = DB::table('dados_agregados')
                ->where([
                    'id_cliente'     => $d->id_cliente,
                    'id_equipamento' => $d->id_equipamento,
                    'periodo_inicio' => $d->periodo_inicio
                ])->first();

            // Build dinâmico
            $insert = [
                'id_cliente'     => $d->id_cliente,
                'id_equipamento' => $d->id_equipamento,
                'periodo_inicio' => $d->periodo_inicio,
                'periodo_fim'    => $periodoFim,
                'updated_at'     => now()
            ];

            foreach ($d as $k => $v) {
                if (!in_array($k, ['id_cliente','id_equipamento','periodo_inicio','registros_contagem'])) {
                    $insert[$k] = $v;
                }
            }

            // últimas leituras
            foreach ((array)$ultima as $k => $v) {
                if ($k !== 'id' && $k !== 'timestamp' && $k !== 'agregado') {
                    $insert["{$k}_ultima"] = $v;
                }
            }

            if ($exist) {
                $insert['registros_contagem'] = $exist->registros_contagem + $d->registros_contagem;
                DB::table('dados_agregados')->where('id', $exist->id)->update($insert);
            } else {
                $insert['registros_contagem'] = $d->registros_contagem;
                $insert['created_at'] = now();
                DB::table('dados_agregados')->insert($insert);
            }
        }

        $this->info("  ✓ {$tabela} finalizada");
    }

    private function marcarAgregado()
    {
        $tabelas = [
            'corrente_brunidores',
            'corrente_descascadores',
            'corrente_polidores',
            'temperaturas',
            'umidades',
            'grandezas_eletricas'
        ];

        $this->info("\n→ Marcando registros como agregados...");
        foreach ($tabelas as $t) {
            $qtd = DB::table($t)->where('agregado',0)->update(['agregado'=>1]);
            if ($qtd > 0) $this->info("  {$t}: {$qtd}");
        }
    }

    private function limparAntigos()
    {
        $limite = now()->subDays(30);
        $tabelas = [
            'corrente_brunidores',
            'corrente_descascadores',
            'corrente_polidores',
            'temperaturas',
            'umidades',
            'grandezas_eletricas'
        ];

        $this->info("\n→ Limpando registros > 30 dias");

        foreach ($tabelas as $t) {
            $qtd = DB::table($t)
                ->where('agregado',1)
                ->where('timestamp','<',$limite)
                ->delete();

            if ($qtd > 0) $this->info("  {$t}: {$qtd}");
        }
    }
}
