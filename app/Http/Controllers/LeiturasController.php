<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Artisan;

class LeiturasController extends Controller
{
    public function index()
    {
        // ALTERAÇÃO CRÍTICA: Mudando de 'sensor_leituras_agregado' para a tabela correta 'dados_agregados'
        $leituras = DB::table('dados_agregados')
            ->orderByDesc('periodo_inicio')
            ->limit(24)
            ->get();

        return view('leituras.index', compact('leituras'));
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
}
