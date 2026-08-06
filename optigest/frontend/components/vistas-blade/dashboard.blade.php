@extends('layouts.app')

@section('titulo', 'Dashboard')

@section('contenido')
<h2 class="h4 mb-4">Panel Gerencial — KPIs Operativos</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tickets abiertos</div>
                <div class="h3 mb-0">{{ $kpis['tickets_abiertos'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Completados este mes</div>
                <div class="h3 mb-0">{{ $kpis['tickets_completados_mes'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Cotizaciones pendientes</div>
                <div class="h3 mb-0">{{ $kpis['cotizaciones_pendientes'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 {{ $kpis['materiales_bajo_stock'] > 0 ? 'border-danger' : '' }}">
            <div class="card-body">
                <div class="text-muted small">Materiales bajo stock</div>
                <div class="h3 mb-0 {{ $kpis['materiales_bajo_stock'] > 0 ? 'text-danger' : '' }}">
                    {{ $kpis['materiales_bajo_stock'] }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Valor total de inventario</div>
                <div class="h4">Q{{ number_format($kpis['valor_inventario'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tiempo promedio de cotización aprobada</div>
                <div class="h4">{{ $tiempoPromedioCotizacionHoras }} hrs</div>
                <div class="text-muted small">Meta del proyecto: &lt; 0.5 hrs (30 min)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tickets por estado</div>
                @forelse($ticketsPorEstado as $estado => $total)
                    <div class="d-flex justify-content-between small">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $estado) }}</span>
                        <strong>{{ $total }}</strong>
                    </div>
                @empty
                    <span class="text-muted small">Sin datos aún</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-exclamation-triangle text-danger"></i> Alertas de bajo inventario
            </div>
            <div class="list-group list-group-flush">
                @forelse($materialesBajoStock as $material)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $material->nombre }}</span>
                        <span class="badge bg-danger rounded-pill">{{ $material->stock }} / mín. {{ $material->stock_minimo }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Sin alertas de bajo stock por el momento.</div>
                @endforelse
            </div>
            <div class="card-footer bg-white text-end">
                <a href="{{ route('inventario.reportes.stock') }}" class="small">Ver reporte completo →</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tools"></i> Tickets recientes
            </div>
            <div class="list-group list-group-flush">
                @forelse($ticketsRecientes as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>{{ $ticket->codigo }} — {{ $ticket->cliente }}</span>
                        <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $ticket->estado) }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">No hay tickets registrados todavía.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
