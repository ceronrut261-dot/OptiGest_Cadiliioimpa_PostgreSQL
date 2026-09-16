```php
@extends('layouts.app')

@section('titulo', 'Materiales')

@section('contenido')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="h4 mb-0">Materiales de Inventario</h2>
    <a href="{{ route('inventario.materiales.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo material
    </a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Buscar por nombre, código o categoría">
    </div>
    <div class="col-md-3">
        <div class="form-check mt-1">
            <input type="checkbox" name="bajo_stock" value="1" class="form-check-input" id="bajoStock" {{ request('bajo_stock') ? 'checked' : '' }}>
            <label class="form-check-label small" for="bajoStock">Solo bajo stock ({{ $totalBajoStock }})</label>
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary btn-sm w-100">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle">
        <thead class="table-light">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Stock</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($materiales as $material)
                <tr class="{{ $material->bajo_stock ? 'table-danger' : '' }}">
                    <td>{{ $material->codigo }}</td>
                    <td>{{ $material->nombre }}</td>
                    <td>{{ $material->categoria }}</td>
                    <td>{{ $material->proveedor?->nombre ?? '—' }}</td>
                    <td class="text-end">Q{{ number_format($material->precio, 2) }}</td>
                    <td class="text-end">
                        {{ $material->stock }}
                        @if($material->bajo_stock)
                            <span class="badge bg-danger">bajo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('inventario.materiales.edit', $material) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        <a href="{{ route('inventario.materiales.historial', $material) }}" class="btn btn-sm btn-outline-secondary">Historial</a>
                        <form action="{{ route('inventario.materiales.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar este material?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay materiales registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $materiales->links() }}

@endsection
```
