@extends('layouts.app')
@section('titulo', 'Precios por proveedor')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Precios por proveedor — {{ $material->nombre }} ({{ $material->codigo }})</h2>
    <a href="{{ route('inventario.materiales.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<p class="text-muted small">
    Precio de catálogo actual (proveedor asignado en la ficha del material):
    <strong>{{ $material->proveedor->nombre ?? 'Sin proveedor asignado' }} — Q{{ number_format($material->precio, 2) }}</strong>
</p>

<div class="table-responsive mb-4">
<table class="table table-sm bg-white">
    <thead class="table-light"><tr><th>Proveedor</th><th class="text-end">Precio</th><th>Actualizado</th><th></th></tr></thead>
    <tbody>
    @forelse($precios as $i => $precio)
        <tr class="{{ $i === 0 ? 'table-success' : '' }}">
            <td>
                {{ $precio->proveedor->nombre }}
                @if($i === 0)<span class="badge bg-success ms-1">Más barato</span>@endif
            </td>
            <td class="text-end">Q{{ number_format($precio->precio, 2) }}</td>
            <td>{{ $precio->actualizado_en->format('d/m/Y') }}</td>
            <td class="text-end">
                <form action="{{ route('inventario.materiales.precios.eliminar', [$material, $precio]) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este precio?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center text-muted py-3">Aún no hay precios de proveedores registrados para este material.</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<h3 class="h6">Agregar / actualizar precio de un proveedor</h3>
<form action="{{ route('inventario.materiales.precios.guardar', $material) }}" method="POST" class="row g-2 bg-white p-3 rounded shadow-sm" style="max-width:520px;">
    @csrf
    <div class="col-6">
        <select name="proveedor_id" class="form-select form-select-sm" required>
            <option value="">— Proveedor —</option>
            @foreach($proveedores as $proveedor)
                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <input type="number" step="0.01" min="0" name="precio" class="form-control form-control-sm" placeholder="Precio Q" required>
    </div>
    <div class="col-2">
        <button class="btn btn-sm btn-primary w-100">Guardar</button>
    </div>
</form>
@endsection