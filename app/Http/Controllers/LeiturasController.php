<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Artisan;

class LeiturasController extends Controller
{
    public function index()
    {
        $leituras = DB::table('dados_agregados')
            ->orderByDesc('periodo_inicio')
            ->limit(120)
            ->get();

        return view('leituras.index', ['leituras' => $leituras, 'totalLeituras' => $leituras->count()]);
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
