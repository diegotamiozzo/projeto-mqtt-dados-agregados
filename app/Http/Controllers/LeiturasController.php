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
    public function index(Request $request)
    {
        $query = DB::table('dados_agregados');

        // ======================================
        // 1. Recupera usuário logado
        // ======================================
        $user = Auth::user();
        $clienteAtual = $user->external_client_id; // <-- CLIENTE DO USER LOGADO

        if (!$clienteAtual) {
            return back()->withErrors('Usuário não possui external_client_id configurado.');
        }

        // ======================================
        // 2. Aplica filtro fixo pelo cliente logado
        // ======================================
        $query->where('id_cliente', $clienteAtual);

        // ======================================
        // 3. Aplica demais filtros (exceto cliente)
        // ======================================
        $this->applyFilters($query, $request);

        // Limite padrão
        $query->limit(1000);

        // Resultado final
        $leituras = $query->orderByDesc('periodo_inicio')->get();

        // ======================================
        // 4. Filtros de equipamentos (dependem do cliente fixo)
        // ======================================
        $equipamentos = DB::table('dados_agregados')
            ->distinct()
            ->where('id_cliente', $clienteAtual)
            ->orderBy('id_equipamento')
            ->pluck('id_equipamento');

        // ======================================
        // 5. Dados auxiliares
        // ======================================
        $ultimaAtualizacao = DB::table('dados_agregados')->max('updated_at');
        $colunasVisiveis = $this->detectarColunasVisiveis($leituras);

        $nomeEquipamento = null;
        if ($request->filled('id_equipamento')) {
            $nomeEquipamento = $this->obterNomeEquipamento($request->id_equipamento);
        }

        $periodoInfo = $this->calcularPeriodoInfo($leituras, $request);
        $disponibilidade = $this->calcularDisponibilidade($leituras, $colunasVisiveis, $periodoInfo, $request);
        $leiturasComGaps = $this->preencherGaps($leituras, $request);

        // ======================================
        // 6. Cliente único — vindo da tabela users
        // ======================================
        $clientes = collect([$clienteAtual]);

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

    private function applyFilters(Builder $query, Request $request): void
    {
        // ⚠️ REMOVIDO FILTRO DE CLIENTE — AGORA É AUTOMÁTICO

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

    private function detectarColunasVisiveis($leituras)
    {
        $colunas = [
            'brunidores' => false,
            'descascadores' => false,
            'polidores' => false,
            'temperatura' => false,
            'umidade' => false,
            'grandezas_eletricas' => false
        ];

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
                continue;
            }

            $periodosLigados = 0;

            foreach ($leituras as $leitura) {
                if (!is_null($leitura->$campo) && $leitura->$campo > 0) {
                    $periodosLigados++;
                }
            }

            $disponibilidade[$tipo] = round(($periodosLigados / $periodosEsperados) * 100, 2);
        }

        return $disponibilidade;
    }

    private function preencherGaps($leituras, $request)
    {
        if (!$request->filled('data_inicio') || !$request->filled('data_fim')) {
            return $leituras;
        }

        $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
        $dataFim = Carbon::parse($request->data_fim)->endOfDay();

        $leiturasIndexadas = [];
        foreach ($leituras as $leitura) {
            $hora = Carbon::parse($leitura->periodo_inicio)->format('Y-m-d H:00:00');
            $leiturasIndexadas[$hora] = $leitura;
        }

        $resultado = collect();
        $dataAtual = $dataInicio->copy();

        while ($dataAtual <= $dataFim) {
            $chave = $dataAtual->format('Y-m-d H:00:00');

            if (isset($leiturasIndexadas[$chave])) {
                $resultado->push($leiturasIndexadas[$chave]);
            } else {
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
                    'fator_potencia_media' => null,
                ]);
            }

            $dataAtual->addHour();
        }

        return $resultado;
    }

    private function calcularPeriodoInfo($leituras, $request)
    {
        $totalRegistros = $leituras->count();

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

        if ($leituras->isEmpty()) {
            return null;
        }

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

    public function agregar(Request $request)
    {
        Artisan::call('leituras:agregar', [
            '--periodo' => 'hora'
        ]);

        // Mantém filtros (exceto cliente)
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

    public function exportar(Request $request)
    {
        $query = DB::table('dados_agregados');

        // Adiciona filtro obrigatório do cliente logado
        $user = Auth::user();
        $clienteAtual = $user->external_client_id;

        $query->where('id_cliente', $clienteAtual);

        // Filtros adicionais
        $this->applyFilters($query, $request);

        // Detecção de colunas
        $leiturasParaDeteccao = $query->limit(1000)->get();
        $colunasVisiveis = $this->detectarColunasVisiveis($leiturasParaDeteccao);

        $colunasParaExportar = [
            'id_cliente', 'id_equipamento', 'periodo_inicio', 'periodo_fim', 'registros_contagem'
        ];

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
                'fator_potencia_media', 'fator_potencia_max', 'fator_potencia_min', 'fator_potencia_ultima',
            ]
        ];

        foreach ($gruposDeColunas as $grupo => $colunas) {
            if ($colunasVisiveis[$grupo]) {
                $colunasParaExportar = array_merge($colunasParaExportar, $colunas);
            }
        }

        $colunasParaExportar[] = 'updated_at';

        $queryExport = DB::table('dados_agregados')
            ->select($colunasParaExportar)
            ->where('id_cliente', $clienteAtual)
            ->orderBy('periodo_inicio');

        $this->applyFilters($queryExport, $request);

        $leituras = $queryExport->get();

        $nomeArquivo = 'dados_agregados_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ];

        $callback = function () use ($leituras, $colunasParaExportar) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($leituras->isNotEmpty()) {
                fputcsv($file, $colunasParaExportar, ';');
            }

            foreach ($leituras as $leitura) {
                fputcsv($file, (array) $leitura, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
