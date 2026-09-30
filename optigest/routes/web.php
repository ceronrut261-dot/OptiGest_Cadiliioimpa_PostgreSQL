<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventarioImportController;

Route::get('/inventario/importar', [InventarioImportController::class, 'form'])
    ->name('inventario.importar.form');

Route::post('/inventario/importar', [InventarioImportController::class, 'import'])
    ->name('inventario.importar');
