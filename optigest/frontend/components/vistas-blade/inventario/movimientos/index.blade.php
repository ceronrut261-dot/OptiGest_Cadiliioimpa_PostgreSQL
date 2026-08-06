@extends('layouts.app')

@section('titulo', 'Movimientos de Inventario')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Movimientos de Inventario</h2>
    <a href="{{ route('inventario.movimientos.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo movimiento
    </a>
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th>Material</th>
                <th>Tipo</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Stock resultante</th>
                <th>Usuario</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
                    <td>{{ $mov->material->nombre }}</td>
                    <td>
                        <span class="badge {{ $mov->tipo === 'entrada' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($mov->tipo) }}
                        </span>
                    </td>
                    <td class="text-end">{{ $mov->cantidad }}</td>
                    <td class="text-end">{{ $mov->stock_resultante }}</td>
                    <td>{{ $mov->usuario->name }}</td>
                    <td class="small text-muted">
                        {{ $mov->motivo }}
                        @if($mov->cotizacion)
                            <br><span class="badge bg-info text-dark">Cotización {{ $mov->cotizacion->codigo }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay movimientos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $movimientos->links() }}
@endsection
