<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Artisan;
use Carbon\Carbon;

class LeiturasController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('dados_agregados');

        // Aplica filtros com conversão de timezone
        $this->applyFilters($query, $request);

        // Aplica limite apenas se não houver filtro de data
        if (!$request->filled('data_inicio') && !$request->filled('data_fim')) {
            $query->limit(24);
        }

        $leituras = $query->orderByDesc('periodo_inicio')->get();

        // Busca dados para preencher os filtros
        $clientes = DB::table('dados_agregados')->distinct()->orderBy('id_cliente')->pluck('id_cliente');

        // Busca equipamentos filtrados por cliente, se houver cliente selecionado
        $equipamentos = DB::table('dados_agregados')
            ->distinct()
            ->when($request->filled('id_cliente'), function ($query) use ($request) {
                return $query->where('id_cliente', $request->id_cliente);
            })
            ->orderBy('id_equipamento')
            ->pluck('id_equipamento');

        // Busca o timestamp da última agregação bem-sucedida
        $ultimaAtualizacao = DB::table('dados_agregados')->max('updated_at');

        // Detecta quais colunas têm dados para otimizar a exibição
        $colunasVisiveis = $this->detectarColunasVisiveis($leituras);

        // Obter nome do equipamento selecionado
        $nomeEquipamento = null;
        if ($request->filled('id_equipamento')) {
            $nomeEquipamento = $this->obterNomeEquipamento($request->id_equipamento);
        }

        // Calcula disponibilidade dos equipamentos com corrente
        $disponibilidade = $this->calcularDisponibilidade($leituras, $colunasVisiveis);

        return view('leituras.index', [
            'leituras' => $leituras,
            'totalLeituras' => $leituras->count(),
            'clientes' => $clientes,
            'equipamentos' => $equipamentos,
            'filters' => $request->all(),
            'ultimaAtualizacao' => $ultimaAtualizacao,
            'colunasVisiveis' => $colunasVisiveis,
            'nomeEquipamento' => $nomeEquipamento,
            'disponibilidade' => $disponibilidade
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }
        if ($request->filled('id_equipamento')) {
            $query->where('id_equipamento', $request->id_equipamento);
        }
        if ($request->filled('data_inicio')) {
            // Converte a data de início para o início do dia em São Paulo e depois para UTC
            $dataInicio = Carbon::parse($request->data_inicio, 'America/Sao_Paulo')->startOfDay()->utc();
            $query->where('periodo_inicio', '>=', $dataInicio);
        }
        if ($request->filled('data_fim')) {
            // Converte a data de fim para o final do dia em São Paulo e depois para UTC
            $dataFim = Carbon::parse($request->data_fim, 'America/Sao_Paulo')->endOfDay()->utc();
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

    private function calcularDisponibilidade($leituras, $colunasVisiveis)
    {
        $disponibilidade = [
            'brunidores' => null,
            'descascadores' => null,
            'polidores' => null,
        ];

        $tiposEquipamento = [
            'brunidores' => 'corrente_brunidores_media',
            'descascadores' => 'corrente_descascadores_media',
            'polidores' => 'corrente_polidores_media',
        ];

        foreach ($tiposEquipamento as $tipo => $campo) {
            if (!$colunasVisiveis[$tipo]) {
                continue;
            }

            $totalPeriodos = 0;
            $periodosLigados = 0;

            foreach ($leituras as $leitura) {
                if (!is_null($leitura->$campo)) {
                    $totalPeriodos++;
                    if ($leitura->$campo > 0) {
                        $periodosLigados++;
                    }
                }
            }

            if ($totalPeriodos > 0) {
                $disponibilidade[$tipo] = round(($periodosLigados / $totalPeriodos) * 100, 2);
            }
        }

        return $disponibilidade;
    }

    public function agregar(Request $request)
    {
        Artisan::call('leituras:agregar', [
            '--periodo' => 'hora'
        ]);

        // Monta os parâmetros de query para manter os filtros
        $queryParams = [];
        
        if ($request->filled('id_cliente')) {
            $queryParams['id_cliente'] = $request->id_cliente;
        }
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
        
        // Reutiliza a lógica de filtros com conversão de timezone
        $this->applyFilters($query, $request);

        $leituras = $query->orderBy('periodo_inicio')->get();
        $nomeArquivo = 'dados_agregados_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ];

        $callback = function () use ($leituras) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($leituras->isNotEmpty()) {
                $primeiraLeitura = (array) $leituras->first();
                $colunas = array_keys($primeiraLeitura);
                fputcsv($file, $colunas, ';');
            }

            foreach ($leituras as $leitura) {
                fputcsv($file, (array) $leitura, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}