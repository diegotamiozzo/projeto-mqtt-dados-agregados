<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeiturasController;
use App\Http\Controllers\LoginmqttController;

Route::middleware(['verify.token'])->group(function () {
    Route::get('/', [LeiturasController::class, 'index'])->name('leituras.index');
    Route::post('/agregar-leituras', [LeiturasController::class, 'agregar'])->name('leituras.agregar');
    Route::get('/agregar-leituras', function() {
        return redirect()->route('leituras.index', array_filter([
            'token' => session('auth_token'),
            'email' => session('user_email')
        ]));
    });
    Route::get('/exportar-leituras', [LeiturasController::class, 'exportar'])->name('leituras.exportar');
    Route::get('/dashboard', [LoginmqttController::class, 'index'])->name('dashboard.index');
});
