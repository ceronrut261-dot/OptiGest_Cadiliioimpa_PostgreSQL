@extends('layouts.app')
@section('titulo', 'Cotización ' . $cotizacion->codigo)
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotización {{ $cotizacion->codigo }}</h2>
    <div>
        <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" class="btn btn-outline-danger btn-sm">PDF</a>
        @if($cotizacion->estado !== 'aprobada' && $cotizacion->estado !== 'rechazada')
            <form action="{{ route('cotizaciones.aprobar', $cotizacion) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">Aprobar</button>
            </form>
            <form action="{{ route('cotizaciones.rechazar', $cotizacion) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <button class="btn btn-outline-danger btn-sm">Rechazar</button>
            </form>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> {{ $cotizacion->cliente->nombre }}</p>
        <p><strong>Estado:</strong> <span class="badge bg-secondary text-capitalize">{{ $cotizacion->estado }}</span></p>
        <p><strong>Cotizador:</strong> {{ $cotizacion->cotizador->name }}</p>
        @if($cotizacion->ticket)
            <p><strong>Ticket relacionado:</strong> <a href="{{ route('tickets.show', $cotizacion->ticket) }}">{{ $cotizacion->ticket->codigo }}</a></p>
        @endif
    </div>
</div>

<div class="table-responsive">
<table class="table table-sm bg-white">
    <thead class="table-light"><tr><th>Material</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    @foreach($cotizacion->detalles as $detalle)
        <tr>
            <td>{{ $detalle->material->nombre }}</td>
            <td class="text-end">{{ $detalle->cantidad }}</td>
            <td class="text-end">Q{{ number_format($detalle->precio_unitario,2) }}</td>
            <td class="text-end">Q{{ number_format($detalle->subtotal,2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end">Q{{ number_format($cotizacion->total,2) }}</th></tr>
    </tfoot>
</table>
</div>
@endsection
