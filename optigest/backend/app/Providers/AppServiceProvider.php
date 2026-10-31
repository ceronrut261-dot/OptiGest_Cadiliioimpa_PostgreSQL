<?php

namespace App\Providers;

use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\Proveedor;
use App\Models\Ticket;
use App\Observers\AuditoriaObserver;
use App\Observers\MaterialObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Auditoría de actividades
        Material::observe(AuditoriaObserver::class);
        Proveedor::observe(AuditoriaObserver::class);
        Ticket::observe(AuditoriaObserver::class);
        Cotizacion::observe(AuditoriaObserver::class);

        // Historial de precios de materiales
        Material::observe(MaterialObserver::class);
    }
}
