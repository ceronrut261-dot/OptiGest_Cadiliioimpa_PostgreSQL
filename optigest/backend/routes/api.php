<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IA\AsistenteController;

Route::post('/ia/chat', [AsistenteController::class, 'procesarMensaje']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
