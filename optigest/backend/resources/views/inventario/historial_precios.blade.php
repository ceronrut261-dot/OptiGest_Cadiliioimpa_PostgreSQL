@extends('layouts.app')

@section('titulo', 'Historial de precios')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Historial de precios — {{ $material->nombre }} ({{ $material->codigo }})</h2>
    <a href="{{ route('inventario.materiales.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
</div>

<p>Precio actual: <strong>Q{{ number_format($material->precio, 2) }}</strong></p>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th class="text-end">Precio anterior</th>
                <th class="text-end">Precio nuevo</th>
                <th>Proveedor</th>
                <th>Usuario</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historial as $registro)
                <tr>
                    <td>{{ $registro->fecha->format('d/m/Y H:i') }}</td>
                    <td class="text-end">Q{{ number_format($registro->precio_anterior, 2) }}</td>
                    <td class="text-end">Q{{ number_format($registro->precio_nuevo, 2) }}</td>
                    <td>{{ $registro->proveedor->nombre ?? '—' }}</td>
                    <td>{{ $registro->usuario->name ?? '—' }}</td>
                    <td>{{ $registro->motivo }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Este material aún no tiene historial de precios.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
