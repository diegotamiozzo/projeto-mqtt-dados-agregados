<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Artisan;
use Carbon\Carbon;

class LeiturasController extends Controller
{
    /**
     * Página principal: lista leituras, aplica filtros,
     * calcula dados adicionais e envia tudo para a view.
     */
    public function index(Request $request)
    {
        // Base da consulta (ler tabela agregada)
        $query = DB::table('dados_agregados');

        // Obtém usuário autenticado
        $user = Auth::user();
        $clienteAtual = $user->external_client_id;

        // Garante que o usuário pertence a um cliente
        if (!$clienteAtual) {
            return back()->withErrors('Usuário não possui external_client_id configurado.');
        }

        // Filtra sempre pelo cliente atual
        $query->where('id_cliente', $clienteAtual);

        // Aplica filtros opcionais (equipamento e datas)
        $this->applyFilters($query, $request);

        // Evita retorno massivo (melhor performance)
        $query->limit(1000);

        // Executa consulta
        $leituras = $query->orderByDesc('periodo_inicio')->get();

        // Lista de equipamentos disponíveis para o cliente
        $equipamentos = DB::table('dados_agregados')
            ->distinct()
            ->where('id_cliente', $clienteAtual)
            ->orderBy('id_equipamento')
            ->pluck('id_equipamento');

        // Última atualização da tabela
        $ultimaAtualizacao = DB::table('dados_agregados')->max('updated_at');

        // Detecta quais colunas têm dados (para exibição dinâmica)
        $colunasVisiveis = $this->detectarColunasVisiveis($leituras);

        // Nome formatado do equipamento (Ex: BRUNIDOR_01 → Brunidor 01)
        $nomeEquipamento = null;
        if ($request->filled('id_equipamento')) {
            $nomeEquipamento = $this->obterNomeEquipamento($request->id_equipamento);
        }

        // Calcula informações de período (dias, horas etc.)
        $periodoInfo = $this->calcularPeriodoInfo($leituras, $request);

        // Calcula percentual de disponibilidade por tipo de máquina
        $disponibilidade = $this->calcularDisponibilidade($leituras, $colunasVisiveis, $periodoInfo, $request);

        // Preenche horas faltantes com registros nulos (para gráficos)
        $leiturasComGaps = $this->preencherGaps($leituras, $request);

        // Apenas um cliente retornado, compatível com futuras multi-tenants
        $clientes = collect([$clienteAtual]);

        // Retorna à view
        return view('leituras.index', [
            'leituras' => $leituras,
            'leiturasGrafico' => $leiturasComGaps,
            'totalLeituras' => $leituras->count(),
            'clientes' => $clientes,
            'equipamentos' => $equipamentos,
            'filters' => $request->all(),
            'ultimaAtualizacao' => $ultimaAtualizacao,
            'colunasVisiveis' => $colunasVisiveis,
            'nomeEquipamento' => $nomeEquipamento,
            'disponibilidade' => $disponibilidade,
            'periodoInfo' => $periodoInfo,
            'clienteAtual' => $clienteAtual,
        ]);
    }

    /**
     * Aplica filtros opcionais à consulta SQL.
     * Filtros: id_equipamento, data_inicio, data_fim.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('id_equipamento')) {
            $query->where('id_equipamento', $request->id_equipamento);
        }
        if ($request->filled('data_inicio')) {
            $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
            $query->where('periodo_inicio', '>=', $dataInicio);
        }
        if ($request->filled('data_fim')) {
            $dataFim = Carbon::parse($request->data_fim)->endOfDay();
            $query->where('periodo_inicio', '<=', $dataFim);
        }
    }

    /**
     * Verifica quais grupos de colunas possuem dados
     * para determinar quais cards / gráficos mostrar na view.
     */
    private function detectarColunasVisiveis($leituras)
    {
        // Flags indicando se existe alguma leitura válida em cada categoria
        $colunas = [
            'brunidores' => false,
            'descascadores' => false,
            'polidores' => false,
            'temperatura' => false,
            'umidade' => false,
            'grandezas_eletricas' => false
        ];

        // Verifica se algum registro possui valores não-nulos
        foreach ($leituras as $leitura) {
            if (!is_null($leitura->corrente_brunidores_media)) {
                $colunas['brunidores'] = true;
            }
            if (!is_null($leitura->corrente_descascadores_media)) {
                $colunas['descascadores'] = true;
            }
            if (!is_null($leitura->corrente_polidores_media)) {
                $colunas['polidores'] = true;
            }
            if (!is_null($leitura->temperatura_media)) {
                $colunas['temperatura'] = true;
            }
            if (!is_null($leitura->umidade_media)) {
                $colunas['umidade'] = true;
            }
            if (!is_null($leitura->tensao_r_media)) {
                $colunas['grandezas_eletricas'] = true;
            }
        }

        return $colunas;
    }

    /**
     * Formata o nome de um equipamento a partir do ID.
     * Exemplo: "BRUNIDOR_1" → "Brunidor 01".
     */
    private function obterNomeEquipamento($idEquipamento)
    {
        $partes = explode('_', $idEquipamento);

        if (count($partes) >= 2) {
            $tipo = ucfirst($partes[0]);
            $numero = str_pad($partes[1], 2, '0', STR_PAD_LEFT);
            return $tipo . ' ' . $numero;
        }

        return $idEquipamento;
    }

    /**
     * Calcula a disponibilidade de cada tipo de máquina.
     * Disponibilidade = horas ligadas / horas totais filtradas.
     */
    private function calcularDisponibilidade($leituras, $colunasVisiveis, $periodoInfo, $request)
    {
        $disponibilidade = [
            'brunidores' => null,
            'descascadores' => null,
            'polidores' => null,
        ];

        if (!$periodoInfo) {
            return $disponibilidade;
        }

        // Campos avaliados para cada tipo
        $tiposEquipamento = [
            'brunidores' => 'corrente_brunidores_media',
            'descascadores' => 'corrente_descascadores_media',
            'polidores' => 'corrente_polidores_media',
        ];

        $periodosEsperados = $periodoInfo['horas_filtradas'];

        if ($periodosEsperados <= 0) {
            return $disponibilidade;
        }

        foreach ($tiposEquipamento as $tipo => $campo) {
            if (!$colunasVisiveis[$tipo]) {
                continue; // Ignora se não há dados deste tipo
            }

            $periodosLigados = 0;

            // Conta quantos períodos tiveram corrente > 0
            foreach ($leituras as $leitura) {
                if (!is_null($leitura->$campo) && $leitura->$campo > 0) {
                    $periodosLigados++;
                }
            }

            $disponibilidade[$tipo] = round(($periodosLigados / $periodosEsperados) * 100, 2);
        }

        return $disponibilidade;
    }

    /**
     * Preenche horas faltantes entre data_inicio e data_fim
     * criando registros "nulos" para gráficos ficarem contínuos.
     */
    private function preencherGaps($leituras, $request)
    {
        if (!$request->filled('data_inicio') || !$request->filled('data_fim')) {
            return $leituras; // Nenhum preenchimento necessário
        }

        $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
        $dataFim = Carbon::parse($request->data_fim)->endOfDay();

        // Indexa leituras por hora
        $leiturasIndexadas = [];
        foreach ($leituras as $leitura) {
            $hora = Carbon::parse($leitura->periodo_inicio)->format('Y-m-d H:00:00');
            $leiturasIndexadas[$hora] = $leitura;
        }

        $resultado = collect();
        $dataAtual = $dataInicio->copy();

        // Gera cada hora do período
        while ($dataAtual <= $dataFim) {
            $chave = $dataAtual->format('Y-m-d H:00:00');

            if (isset($leiturasIndexadas[$chave])) {
                // Já existe leitura desta hora
                $resultado->push($leiturasIndexadas[$chave]);
            } else {
                // Cria registro vazio (todos campos nulos)
                $resultado->push((object)[
                    'periodo_inicio' => $dataAtual->copy()->toDateTimeString(),
                    'periodo_fim' => $dataAtual->copy()->addHour()->toDateTimeString(),
                    'corrente_brunidores_media' => null,
                    'corrente_descascadores_media' => null,
                    'corrente_polidores_media' => null,
                    'temperatura_media' => null,
                    'umidade_media' => null,
                    'tensao_r_media' => null,
                    'tensao_s_media' => null,
                    'tensao_t_media' => null,
                    'corrente_r_media' => null,
                    'corrente_s_media' => null,
                    'corrente_t_media' => null,
                    'potencia_ativa_media' => null,
                    'potencia_reativa_media' => null,
                    'potencia_aparente_media' => null,
                    'fator_potencia_media' => null,
                ]);
            }

            $dataAtual->addHour();
        }

        return $resultado;
    }

    /**
     * Calcula datas e quantidade de horas/dias do período filtrado.
     */
    private function calcularPeriodoInfo($leituras, $request)
    {
        $totalRegistros = $leituras->count();

        // Caso usuário tenha escolhido um período manualmente
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $dataInicioFiltro = \Carbon\Carbon::parse($request->data_inicio)->startOfDay();
            $dataFimFiltro = \Carbon\Carbon::parse($request->data_fim)->endOfDay();

            $diasDiferenca = ceil($dataInicioFiltro->diffInDays($dataFimFiltro, true));
            $horasDiferenca = ceil($dataInicioFiltro->diffInHours($dataFimFiltro, true));

            return [
                'dataInicio' => $dataInicioFiltro->format('d/m/Y H:i'),
                'dataFim' => $dataFimFiltro->format('d/m/Y H:i'),
                'totalRegistros' => $totalRegistros,
                'dias' => $diasDiferenca,
                'horas' => $horasDiferenca,
                'horas_filtradas' => $horasDiferenca,
            ];
        }

        // Caso não tenha filtro e não existam registros
        if ($leituras->isEmpty()) {
            return null;
        }

        // Período baseado nas leituras disponíveis
        $primeiraLeitura = $leituras->last();
        $ultimaLeitura = $leituras->first();

        $dataInicio = \Carbon\Carbon::parse($primeiraLeitura->periodo_inicio);
        $dataFim = \Carbon\Carbon::parse($ultimaLeitura->periodo_fim);

        $diasDiferenca = $dataInicio->diffInDays($dataFim);
        $horasDiferenca = $dataInicio->diffInHours($dataFim);

        return [
            'dataInicio' => $dataInicio->format('d/m/Y H:i'),
            'dataFim' => $dataFim->format('d/m/Y H:i'),
            'totalRegistros' => $totalRegistros,
            'dias' => $diasDiferenca,
            'horas' => $horasDiferenca,
            'horas_filtradas' => $horasDiferenca,
        ];
    }

    /**
     * Executa o comando Artisan que agrega registros por hora.
     * Depois retorna à tela preservando filtros.
     */
    public function agregar(Request $request)
    {
        // Comando customizado que gera médias/valores agregados
        Artisan::call('leituras:agregar', [
            '--periodo' => 'hora'
        ]);

        // Mantém filtros da tela
        $queryParams = [];

        if ($request->filled('id_equipamento')) {
            $queryParams['id_equipamento'] = $request->id_equipamento;
        }
        if ($request->filled('data_inicio')) {
            $queryParams['data_inicio'] = $request->data_inicio;
        }
        if ($request->filled('data_fim')) {
            $queryParams['data_fim'] = $request->data_fim;
        }

        return redirect()->route('leituras.index', $queryParams)
            ->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Exporta dados filtrados em formato CSV.
     * Exportação inclui apenas colunas visíveis (detecção dinâmica).
     */
    public function exportar(Request $request)
    {
        $query = DB::table('dados_agregados');

        $user = Auth::user();
        $clienteAtual = $user->external_client_id;

        // Filtra por cliente
        $query->where('id_cliente', $clienteAtual);

        // Aplica filtros usuais
        $this->applyFilters($query, $request);

        // Detecta colunas com base nas primeiras 1000 leituras
        $leiturasParaDeteccao = $query->limit(1000)->get();
        $colunasVisiveis = $this->detectarColunasVisiveis($leiturasParaDeteccao);

        // Colunas básicas sempre exportadas
        $colunasParaExportar = [
            'id_cliente', 'id_equipamento', 'periodo_inicio', 'periodo_fim', 'registros_contagem'
        ];

        // Mapeamento dos grupos
        $gruposDeColunas = [
            'brunidores' => ['corrente_brunidores_media', 'corrente_brunidores_max', 'corrente_brunidores_min', 'corrente_brunidores_ultima'],
            'descascadores' => ['corrente_descascadores_media', 'corrente_descascadores_max', 'corrente_descascadores_min', 'corrente_descascadores_ultima'],
            'polidores' => ['corrente_polidores_media', 'corrente_polidores_max', 'corrente_polidores_min', 'corrente_polidores_ultima'],
            'temperatura' => ['temperatura_media', 'temperatura_max', 'temperatura_min', 'temperatura_ultima'],
            'umidade' => ['umidade_media', 'umidade_max', 'umidade_min', 'umidade_ultima'],
            'grandezas_eletricas' => [
                'tensao_r_media', 'tensao_r_max', 'tensao_r_min', 'tensao_r_ultima',
                'corrente_r_media', 'corrente_r_max', 'corrente_r_min', 'corrente_r_ultima',
                'tensao_s_media', 'tensao_s_max', 'tensao_s_min', 'tensao_s_ultima',
                'corrente_s_media', 'corrente_s_max', 'corrente_s_min', 'corrente_s_ultima',
                'tensao_t_media', 'tensao_t_max', 'tensao_t_min', 'tensao_t_ultima',
                'corrente_t_media', 'corrente_t_max', 'corrente_t_min', 'corrente_t_ultima',
                'potencia_ativa_media', 'potencia_ativa_max', 'potencia_ativa_min', 'potencia_ativa_ultima',
                'potencia_reativa_media', 'potencia_reativa_max', 'potencia_reativa_min', 'potencia_reativa_ultima',
                'potencia_aparente_media', 'potencia_aparente_max', 'potencia_aparente_min', 'potencia_aparente_ultima',
                'fator_potencia_media', 'fator_potencia_max', 'fator_potencia_min', 'fator_potencia_ultima',
            ]
        ];

        // Adiciona somente colunas cujos grupos existem no dataset
        foreach ($gruposDeColunas as $grupo => $colunas) {
            if ($colunasVisiveis[$grupo]) {
                $colunasParaExportar = array_merge($colunasParaExportar, $colunas);
            }
        }

        // Sempre incluir data de atualização
        $colunasParaExportar[] = 'updated_at';

        // Consulta final para exportação
        $queryExport = DB::table('dados_agregados')
            ->select($colunasParaExportar)
            ->where('id_cliente', $clienteAtual)
            ->orderBy('periodo_inicio');

        $this->applyFilters($queryExport, $request);

        $leituras = $queryExport->get();

        // Nome do arquivo
        $nomeArquivo = 'dados_agregados_' . date('Y-m-d_H-i-s') . '.csv';

        // Cabeçalhos do response
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ];

        // Gera CSV linha a linha
        $callback = function () use ($leituras, $colunasParaExportar) {
            $file = fopen('php://output', 'w');

            // Adiciona BOM UTF-8 para Excel reconhecer
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalho
            if ($leituras->isNotEmpty()) {
                fputcsv($file, $colunasParaExportar, ';');
            }

            // Linhas
            foreach ($leituras as $leitura) {
                fputcsv($file, (array) $leitura, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
