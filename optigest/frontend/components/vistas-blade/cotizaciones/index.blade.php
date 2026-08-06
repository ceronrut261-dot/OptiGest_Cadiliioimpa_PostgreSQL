@extends('layouts.app')
@section('titulo', 'Cotizaciones')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotizaciones</h2>
    <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva cotización</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    @forelse($cotizaciones as $cot)
        <tr>
            <td>{{ $cot->codigo }}</td>
            <td>{{ $cot->cliente }}</td>
            <td><span class="badge bg-secondary text-capitalize">{{ $cot->estado }}</span></td>
            <td class="text-end">Q{{ number_format($cot->total, 2) }}</td>
            <td>{{ $cot->fecha->format('d/m/Y') }}</td>
            <td class="text-end"><a href="{{ route('cotizaciones.show', $cot) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay cotizaciones registradas.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $cotizaciones->links() }}
@endsection
