@extends('layouts.app')

@section('titulo', 'Reporte de Inventario')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Reporte Gerencial de Inventario</h2>
    <a href="{{ route('inventario.reportes.stock.pdf') }}" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Valor total de inventario</div>
                <div class="h4">Q{{ number_format($valorTotalInventario, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Materiales en alerta</div>
                <div class="h4 text-danger">{{ $materialesBajoStock->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Categorías</div>
                <div class="h4">{{ $resumenPorCategoria->count() }}</div>
            </div>
        </div>
    </div>
</div>

<h3 class="h6">Resumen por categoría</h3>
<div class="table-responsive mb-4">
    <table class="table table-sm bg-white">
        <thead class="table-light">
            <tr><th>Categoría</th><th class="text-end">Ítems</th><th class="text-end">Valor</th><th class="text-end">Bajo stock</th></tr>
        </thead>
        <tbody>
            @foreach($resumenPorCategoria as $categoria => $resumen)
                <tr>
                    <td>{{ $categoria }}</td>
                    <td class="text-end">{{ $resumen['cantidad_items'] }}</td>
                    <td class="text-end">Q{{ number_format($resumen['valor_total'], 2) }}</td>
                    <td class="text-end">{{ $resumen['bajo_stock'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<h3 class="h6">Materiales en alerta de bajo stock</h3>
<div class="table-responsive">
    <table class="table table-sm table-danger bg-white">
        <thead><tr><th>Código</th><th>Nombre</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th></tr></thead>
        <tbody>
            @forelse($materialesBajoStock as $material)
                <tr>
                    <td>{{ $material->codigo }}</td>
                    <td>{{ $material->nombre }}</td>
                    <td class="text-end">{{ $material->stock }}</td>
                    <td class="text-end">{{ $material->stock_minimo }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Sin alertas de bajo stock.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
