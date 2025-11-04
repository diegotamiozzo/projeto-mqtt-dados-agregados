<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Artisan;

class LeiturasController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('dados_agregados');

        // Aplica filtros se existirem na requisição
        if ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }
        if ($request->filled('id_equipamento')) {
            $query->where('id_equipamento', $request->id_equipamento);
        }
        if ($request->filled('data_inicio')) {
            $query->where('periodo_inicio', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            // Adiciona a hora final para incluir todo o dia
            $query->where('periodo_inicio', '<=', $request->data_fim . ' 23:59:59');
        }

        $leituras = $query->orderByDesc('periodo_inicio')->limit(120)->get();

        // Busca dados para preencher os filtros
        $clientes = DB::table('dados_agregados')->distinct()->orderBy('id_cliente')->pluck('id_cliente');
        $equipamentos = DB::table('dados_agregados')->distinct()->orderBy('id_equipamento')->pluck('id_equipamento');

        return view('leituras.index', [
            'leituras' => $leituras,
            'totalLeituras' => $leituras->count(),
            'clientes' => $clientes,
            'equipamentos' => $equipamentos,
            'filters' => $request->all() // Envia os filtros aplicados de volta para a view
        ]);
    }

    public function agregar(Request $request)
    {
        // Chamada do comando Artisan permanece correta, usando o comando que criamos
        Artisan::call('leituras:agregar', [
            '--periodo' => 'hora'
        ]);

        return redirect()->route('leituras.index')
            ->with('success', 'Agregação realizada com sucesso!');
    }

    public function exportar(Request $request)
    {
        $query = DB::table('dados_agregados');

        // Aplica os mesmos filtros da index na exportação
        if ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }
        if ($request->filled('id_equipamento')) {
            $query->where('id_equipamento', $request->id_equipamento);
        }
        if ($request->filled('data_inicio')) {
            $query->where('periodo_inicio', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->where('periodo_inicio', '<=', $request->data_fim . ' 23:59:59');
        }
        $leituras = $query->orderBy('periodo_inicio')->get();
        $nomeArquivo = 'dados_agregados_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ];

        $callback = function () use ($leituras) {
            $file = fopen('php://output', 'w');
            
            // Adiciona o BOM para garantir a codificação correta no Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Pega os nomes das colunas da primeira linha de dados, se houver
            if ($leituras->isNotEmpty()) {
                $primeiraLeitura = (array) $leituras->first();
                $colunas = array_keys($primeiraLeitura);
                fputcsv($file, $colunas, ';');
            }

            // Adiciona os dados
            foreach ($leituras as $leitura) {
                fputcsv($file, (array) $leitura, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
