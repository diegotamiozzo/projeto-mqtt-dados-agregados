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
        // ------------------------------------------------------------------------
        // SEGURANÇA E FILTRAGEM AUTOMÁTICA
        // ------------------------------------------------------------------------
        // O Middleware já validou o token e logou o usuário.
        // Agora pegamos o ID do cliente vinculado a este usuário.
        
        $user = Auth::user();
        $clienteIdPermitido = $user->external_client_id ?? null; // Se for NULL, é admin (vê tudo)

        // Se o usuário tem um cliente vinculado, forçamos esse filtro no Request
        // para garantir que ele não consiga burlar trocando o ID na URL.
        if ($clienteIdPermitido) {
            $request->merge(['id_cliente' => $clienteIdPermitido]);
        }

        /*
        |--------------------------------------------------------------------------
        | CONSULTA DE DADOS
        |--------------------------------------------------------------------------
        */

        $query = DB::table('dados_agregados');

        // Aplica filtros (data, equipamento e AGORA O CLIENTE OBRIGATÓRIO)
        $this->applyFilters($query, $request, $clienteIdPermitido);

        $query->limit(1000);
        $leituras = $query->orderByDesc('periodo_inicio')->get();

        /*
        |--------------------------------------------------------------------------
        | FILTRO DE COMBOS (SELECTS)
        |--------------------------------------------------------------------------
        | Os combos de 'Clientes' e 'Equipamentos' também devem respeitar o filtro.
        */

        // Lista de Clientes:
        // Se for admin, vê todos. Se for cliente, vê apenas a si mesmo.
        $queryClientes = DB::table('dados_agregados')->distinct();
        if ($clienteIdPermitido) {
            $queryClientes->where('id_cliente', $clienteIdPermitido);
        }
        $clientes = $queryClientes->orderBy('id_cliente')->pluck('id_cliente');

        // Lista de Equipamentos:
        $queryEquipamentos = DB::table('dados_agregados')->distinct();
        
        // Aplica o filtro de segurança nos equipamentos também
        if ($clienteIdPermitido) {
            $queryEquipamentos->where('id_cliente', $clienteIdPermitido);
        }
        // Se o usuário selecionou um cliente específico no filtro (admins)
        elseif ($request->filled('id_cliente')) {
            $queryEquipamentos->where('id_cliente', $request->id_cliente);
        }

        $equipamentos = $queryEquipamentos->orderBy('id_equipamento')->pluck('id_equipamento');

        // Dados auxiliares
        $ultimaAtualizacao = DB::table('dados_agregados')->max('updated_at');
        $colunasVisiveis = $this->detectarColunasVisiveis($leituras);
        $nomeEquipamento = $request->filled('id_equipamento') ? $this->obterNomeEquipamento($request->id_equipamento) : null;
        $periodoInfo = $this->calcularPeriodoInfo($leituras, $request);
        $disponibilidade = $this->calcularDisponibilidade($leituras, $colunasVisiveis, $periodoInfo, $request);
        $leiturasComGaps = $this->preencherGaps($leituras, $request);

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
            'externalUser' => $user->name // Nome vindo do banco agora
        ]);
    }

    /**
     * Aplica filtros e SEGURANÇA na query
     */
    private function applyFilters(Builder $query, Request $request, ?string $clienteIdPermitido): void
    {
        // 1. REGRA DE OURO: Se tiver ID vinculado, filtra obrigatoriamente
        if ($clienteIdPermitido) {
            $query->where('id_cliente', $clienteIdPermitido);
        } 
        // 2. Se for admin (sem vínculo), aceita o filtro que vier da tela
        elseif ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }

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

    // --- MÉTODOS AUXILIARES (Sem alterações lógicas profundas, apenas mantidos) ---

    private function detectingColunasVisiveis($leituras) { /* Mantido igual */ return $this->detectarColunasVisiveis($leituras); }
    
    private function detectarColunasVisiveis($leituras)
    {
        $colunas = [
            'brunidores' => false, 'descascadores' => false, 'polidores' => false,
            'temperatura' => false, 'umidade' => false, 'grandezas_eletricas' => false
        ];
        foreach ($leituras as $leitura) {
            if (!is_null($leitura->corrente_brunidores_media)) $colunas['brunidores'] = true;
            if (!is_null($leitura->corrente_descascadores_media)) $colunas['descascadores'] = true;
            if (!is_null($leitura->corrente_polidores_media)) $colunas['polidores'] = true;
            if (!is_null($leitura->temperatura_media)) $colunas['temperatura'] = true;
            if (!is_null($leitura->umidade_media)) $colunas['umidade'] = true;
            if (!is_null($leitura->tensao_r_media)) $colunas['grandezas_eletricas'] = true;
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
        $disponibilidade = ['brunidores' => null, 'descascadores' => null, 'polidores' => null];
        if (!$periodoInfo || $periodoInfo['horas_filtradas'] <= 0) return $disponibilidade;

        $tiposEquipamento = [
            'brunidores' => 'corrente_brunidores_media',
            'descascadores' => 'corrente_descascadores_media',
            'polidores' => 'corrente_polidores_media',
        ];

        foreach ($tiposEquipamento as $tipo => $campo) {
            if (!$colunasVisiveis[$tipo]) continue;
            $periodosLigados = 0;
            foreach ($leituras as $leitura) {
                if (!is_null($leitura->$campo) && $leitura->$campo > 0) $periodosLigados++;
            }
            $disponibilidade[$tipo] = round(($periodosLigados / $periodoInfo['horas_filtradas']) * 100, 2);
        }
        return $disponibilidade;
    }

    private function preencherGaps($leituras, $request)
    {
        if (!$request->filled('data_inicio') || !$request->filled('data_fim')) return $leituras;

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
                $emptyObj = new \stdClass();
                $emptyObj->periodo_inicio = $dataAtual->copy()->toDateTimeString();
                $emptyObj->periodo_fim = $dataAtual->copy()->addHour()->toDateTimeString();
                // Preenche nulos para evitar erro no gráfico
                foreach(['corrente_brunidores_media','corrente_descascadores_media','corrente_polidores_media',
                         'temperatura_media','umidade_media','tensao_r_media','tensao_s_media','tensao_t_media',
                         'corrente_r_media','corrente_s_media','corrente_t_media','potencia_ativa_media',
                         'potencia_reativa_media','fator_potencia_media'] as $field) {
                    $emptyObj->$field = null;
                }
                $resultado->push($emptyObj);
            }
            $dataAtual->addHour();
        }
        return $resultado;
    }

    private function calcularPeriodoInfo($leituras, $request)
    {
        $totalRegistros = $leituras->count();
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $start = Carbon::parse($request->data_inicio)->startOfDay();
            $end = Carbon::parse($request->data_fim)->endOfDay();
            $diffH = ceil($start->diffInHours($end, true));
            return [
                'dataInicio' => $start->format('d/m/Y H:i'),
                'dataFim' => $end->format('d/m/Y H:i'),
                'totalRegistros' => $totalRegistros,
                'dias' => ceil($start->diffInDays($end, true)),
                'horas' => $diffH,
                'horas_filtradas' => $diffH
            ];
        }
        if ($leituras->isEmpty()) return null;
        
        $start = Carbon::parse($leituras->last()->periodo_inicio);
        $end = Carbon::parse($leituras->first()->periodo_fim);
        return [
            'dataInicio' => $start->format('d/m/Y H:i'),
            'dataFim' => $end->format('d/m/Y H:i'),
            'totalRegistros' => $totalRegistros,
            'dias' => $start->diffInDays($end),
            'horas' => $start->diffInHours($end),
            'horas_filtradas' => $start->diffInHours($end)
        ];
    }

    public function agregar(Request $request)
    {
        Artisan::call('leituras:agregar', ['--periodo' => 'hora']);
        // Mantém filtros na url
        return redirect()->route('leituras.index', $request->only(['id_cliente', 'id_equipamento', 'data_inicio', 'data_fim']))
            ->with('success', 'Dados atualizados com sucesso!');
    }

    public function exportar(Request $request)
    {
        $user = Auth::user();
        $clienteIdPermitido = $user->external_client_id ?? null;

        $query = DB::table('dados_agregados');
        
        // Aplica a MESMA segurança do index
        $this->applyFilters($query, $request, $clienteIdPermitido);
    
        // Amostra para detectar colunas
        $leiturasParaDeteccao = $query->limit(1000)->get();
        $colunasVisiveis = $this->detectarColunasVisiveis($leiturasParaDeteccao);
    
        // Colunas base
        $colunasParaExportar = ['id_cliente', 'id_equipamento', 'periodo_inicio', 'periodo_fim', 'registros_contagem'];
        
        // Mapeamento de grupos (resumido para caber aqui, lógica mantida)
        $grupos = [
            'brunidores' => ['corrente_brunidores_media', 'corrente_brunidores_max', 'corrente_brunidores_min', 'corrente_brunidores_ultima'],
            'descascadores' => ['corrente_descascadores_media', 'corrente_descascadores_max', 'corrente_descascadores_min', 'corrente_descascadores_ultima'],
            'polidores' => ['corrente_polidores_media', 'corrente_polidores_max', 'corrente_polidores_min', 'corrente_polidores_ultima'],
            'temperatura' => ['temperatura_media', 'temperatura_max', 'temperatura_min', 'temperatura_ultima'],
            'umidade' => ['umidade_media', 'umidade_max', 'umidade_min', 'umidade_ultima'],
            'grandezas_eletricas' => [
                'tensao_r_media', 'tensao_s_media', 'tensao_t_media',
                'corrente_r_media', 'corrente_s_media', 'corrente_t_media',
                'potencia_ativa_media', 'potencia_reativa_media', 'fator_potencia_media'
            ]
        ];
    
        foreach ($grupos as $grupo => $colunas) {
            if ($colunasVisiveis[$grupo]) $colunasParaExportar = array_merge($colunasParaExportar, $colunas);
        }
        $colunasParaExportar[] = 'updated_at';
    
        // Query final de exportação
        $queryExport = DB::table('dados_agregados')
            ->select($colunasParaExportar)
            ->orderBy('periodo_inicio');
        
        // Re-aplica filtros
        $this->applyFilters($queryExport, $request, $clienteIdPermitido);
    
        $leituras = $queryExport->get();
    
        $nomeArquivo = 'dados_' . date('Ymd_Hi') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ];
    
        return response()->stream(function () use ($leituras, $colunasParaExportar) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            if ($leituras->isNotEmpty()) fputcsv($file, $colunasParaExportar, ';');
            foreach ($leituras as $leitura) fputcsv($file, (array) $leitura, ';');
            fclose($file);
        }, 200, $headers);
    }
}