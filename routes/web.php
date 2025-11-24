<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeiturasController;
use App\Http\Middleware\ValidateExternalToken; // <--- 1. Importe a classe aqui

// 2. Use a classe ::class aqui, em vez de 'validate-token'
Route::middleware([ValidateExternalToken::class])->group(function () {

    Route::get('/', [LeiturasController::class, 'index'])
        ->name('leituras.index');

    Route::post('/agregar-leituras', [LeiturasController::class, 'agregar'])
        ->name('leituras.agregar');

    Route::get('/agregar-leituras', function () {
        return redirect()->route('leituras.index');
    });

    Route::get('/exportar-leituras', [LeiturasController::class, 'exportar'])
        ->name('leituras.exportar');
});