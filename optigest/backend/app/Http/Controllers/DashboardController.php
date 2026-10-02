<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\Ticket;
use App\Models\TicketGasto;
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
            'valor_inventario' => Material::where('activo', true)
                ->selectRaw('COALESCE(SUM(precio * stock), 0) as total')
                ->value('total'),
        ];

        $ticketsPorEstado = Ticket::select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total', 'estado');
        $cotizacionesPorEstado = Cotizacion::select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total', 'estado');

        $tiempoPromedioCotizacionHoras = Cotizacion::where('estado', 'aprobada')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 3600) as promedio')
            ->value('promedio');

        // Indicadores de dinero: solo se muestran a administrador/cotizador.
        $veFinanzas = auth()->user()->hasRole(['administrador', 'cotizador']);
        $finanzas = $veFinanzas ? [
            'ingreso_mes' => (float) Cotizacion::where('estado', 'aprobada')
                ->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)
                ->sum('total'),
            'iva_mes' => (float) Cotizacion::where('estado', 'aprobada')
                ->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)
                ->sum('iva_monto'),
            'gastos_por_cobrar' => (float) TicketGasto::where('cobrar_al_cliente', true)
                ->whereNull('cotizacion_id')->sum('monto'),
            'gastos_sin_comprobante' => TicketGasto::whereNull('archivo_path')->count(),
        ] : null;

        return view('dashboard', [
            'veFinanzas' => $veFinanzas,
            'finanzas' => $finanzas,
            'kpis' => $kpis,
            'ticketsPorEstado' => $ticketsPorEstado,
            'cotizacionesPorEstado' => $cotizacionesPorEstado,
            'tiempoPromedioCotizacionHoras' => round($tiempoPromedioCotizacionHoras ?? 0, 2),
            'materialesBajoStock' => Material::bajoStock()->where('activo', true)->orderBy('stock')->take(8)->get(),
            'ticketsRecientes' => Ticket::with('cliente')->orderByDesc('created_at')->take(6)->get(),
        ]);
    }
}
