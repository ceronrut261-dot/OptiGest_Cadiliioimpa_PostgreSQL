<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Clientes\ClienteController;
use App\Http\Controllers\Cotizaciones\CotizacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IA\AsistenteController;
use App\Http\Controllers\Inventario\MaterialController;
use App\Http\Controllers\Inventario\MovimientoInventarioController;
use App\Http\Controllers\Inventario\ReporteInventarioController;
use App\Http\Controllers\Proveedores\ProveedorController;
use App\Http\Controllers\Tickets\TicketController;
use App\Http\Controllers\Inventario\InventarioImportController;
use App\Http\Controllers\Inventario\HistorialPrecioController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\Salidas\SalidaMaterialController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('inventario')->name('inventario.')->group(function () {

        Route::middleware(['role:administrador|cotizador'])->group(function () {

            Route::resource('materiales', MaterialController::class)
                ->parameters(['materiales' => 'material']);

            Route::get(
                'materiales/{material}/historial-precios',
                [HistorialPrecioController::class, 'index']
            )->name('materiales.historial');

            // Gestión de precios por material
            Route::get(
                'materiales/{material}/precios',
                [MaterialController::class, 'precios']
            )->name('materiales.precios');

            Route::post(
                'materiales/{material}/precios',
                [MaterialController::class, 'guardarPrecio']
            )->name('materiales.precios.guardar');

            Route::delete(
                'materiales/{material}/precios/{precio}',
                [MaterialController::class, 'eliminarPrecio']
            )->name('materiales.precios.eliminar');
        });

        Route::middleware(['role:administrador|tecnico|cotizador'])->group(function () {

            Route::get(
                'movimientos',
                [MovimientoInventarioController::class, 'index']
            )->name('movimientos.index');

            Route::get(
                'movimientos/crear',
                [MovimientoInventarioController::class, 'create']
            )->name('movimientos.create');

            Route::post(
                'movimientos',
                [MovimientoInventarioController::class, 'store']
            )->name('movimientos.store');

            // Exportar movimientos a PDF
            Route::get(
                'movimientos/pdf',
                [MovimientoInventarioController::class, 'exportarPdf']
            )->name('movimientos.pdf');
        });

        Route::middleware(['role:administrador'])->group(function () {

            Route::get(
                'reportes/stock',
                [ReporteInventarioController::class, 'stock']
            )->name('reportes.stock');

            Route::get(
                'reportes/stock/exportar',
                [ReporteInventarioController::class, 'exportarPdf']
            )->name('reportes.stock.pdf');
        });

        Route::middleware(['role:administrador'])->group(function () {

            Route::get(
                'importar',
                [InventarioImportController::class, 'form']
            )->name('importar.form');

            Route::post(
                'importar',
                [InventarioImportController::class, 'import']
            )->name('importar');
        });
    });

    Route::middleware(['role:administrador|cotizador'])->group(function () {

        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor']);
    });

    Route::resource('clientes', ClienteController::class)->except(['show']);

    Route::middleware(['role:administrador'])->group(function () {

        Route::patch(
            'clientes/{cliente}/reactivar',
            [ClienteController::class, 'reactivar']
        )->name('clientes.reactivar');
    });

    Route::resource('tickets', TicketController::class);

    Route::patch(
        'tickets/{ticket}/estado',
        [TicketController::class, 'cambiarEstado']
    )->name('tickets.estado');

    Route::middleware(['role:administrador|cotizador'])->group(function () {

        Route::resource('cotizaciones', CotizacionController::class)
            ->parameters(['cotizaciones' => 'cotizacion']);

        Route::patch(
            'cotizaciones/{cotizacion}/aprobar',
            [CotizacionController::class, 'aprobar']
        )->name('cotizaciones.aprobar');

        Route::patch(
            'cotizaciones/{cotizacion}/rechazar',
            [CotizacionController::class, 'rechazar']
        )->name('cotizaciones.rechazar');

        Route::get(
            'cotizaciones/{cotizacion}/pdf',
            [CotizacionController::class, 'exportarPdf']
        )->name('cotizaciones.pdf');
    });

    // Salidas de materiales
    Route::resource('salidas', SalidaMaterialController::class)
        ->only(['index', 'create', 'store']);

    Route::get(
        'salidas/{salida}/pdf',
        [SalidaMaterialController::class, 'exportarPdf']
    )->name('salidas.pdf');

    Route::get(
        'asistente',
        [AsistenteController::class, 'index']
    )->name('asistente.index');

    Route::post(
        'asistente/consultar',
        [AsistenteController::class, 'consultar']
    )->name('asistente.consultar');

    Route::middleware(['role:administrador'])->group(function () {

        Route::get(
            'usuarios/crear',
            [RegisteredUserController::class, 'create']
        )->name('usuarios.create');

        Route::post(
            'usuarios',
            [RegisteredUserController::class, 'store']
        )->name('usuarios.store');

        Route::get(
            'usuarios',
            [UsuarioController::class, 'index']
        )->name('usuarios.index');

        Route::delete(
            'usuarios/{usuario}',
            [UsuarioController::class, 'destroy']
        )->name('usuarios.destroy');

        Route::patch(
            'usuarios/{usuario}/reactivar',
            [UsuarioController::class, 'reactivar']
        )->name('usuarios.reactivar');
    });
});

require __DIR__.'/auth.php';