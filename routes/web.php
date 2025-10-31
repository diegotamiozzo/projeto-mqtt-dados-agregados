<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeiturasController;


Route::get('/', [LeiturasController::class, 'index'])->name('leituras.index');
Route::post('/agregar-leituras', [LeiturasController::class, 'agregar'])->name('leituras.agregar');
