@extends('layouts.app')
@section('titulo', 'Salidas de materiales')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Salidas de materiales</h2>
    <a href="{{ route('salidas.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-up"></i> Nueva salida</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Proyecto</th><th>Entregó</th><th>Recibió</th><th>Fecha</th><th class="text-end">Total</th><th></th></tr></thead>
    <tbody>
    @forelse($salidas as $salida)
        <tr>
            <td>{{ $salida->codigo }}</td>
            <td>{{ $salida->proyecto }}</td>
            <td>{{ $salida->usuario->name }}</td>
            <td>{{ $salida->persona_recibe }}</td>
            <td>{{ $salida->fecha->format('d/m/Y') }}</td>
            <td class="text-end">Q{{ number_format($salida->total, 2) }}</td>
            <td class="text-end">
                <a href="{{ route('salidas.pdf', $salida) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Vale PDF</a>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay salidas registradas.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $salidas->links() }}
@endsection