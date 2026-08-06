<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $kpis = [
            'tickets_abiertos' => Ticket::whereNotIn('estado', ['completado', 'cancelado'])->count(),
            'tickets_completados_mes' => Ticket::where('estado', 'completado')
                ->whereMonth('fecha_completado', now()->month)->count(),
            'cotizaciones_pendientes' => Cotizacion::where('estado', 'enviada')->count(),
            'materiales_bajo_stock' => Material::bajoStock()->where('activo', true)->count(),
            'valor_inventario' => Material::where('activo', true)->get()->sum(fn ($m) => $m->precio * $m->stock),
        ];

        $ticketsPorEstado = Ticket::select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total', 'estado');
        $cotizacionesPorEstado = Cotizacion::select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total', 'estado');

        $tiempoPromedioCotizacionHoras = Cotizacion::where('estado', 'aprobada')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 3600) as promedio')
            ->value('promedio');

        return view('dashboard', [
            'kpis' => $kpis,
            'ticketsPorEstado' => $ticketsPorEstado,
            'cotizacionesPorEstado' => $cotizacionesPorEstado,
            'tiempoPromedioCotizacionHoras' => round($tiempoPromedioCotizacionHoras ?? 0, 2),
            'materialesBajoStock' => Material::bajoStock()->where('activo', true)->orderBy('stock')->take(8)->get(),
            'ticketsRecientes' => Ticket::orderByDesc('created_at')->take(6)->get(),
        ]);
    }
}
