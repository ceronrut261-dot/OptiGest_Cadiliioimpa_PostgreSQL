@extends('layouts.app')
@section('titulo', 'Catálogo de ' . $proveedor->nombre)
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-0">Catálogo de materiales — {{ $proveedor->nombre }}</h2>
        <span class="text-muted small">Lista de lo que vende este proveedor y sus precios de compra</span>
    </div>
    <a href="{{ route('proveedores.index') }}" class="btn btn-sm btn-outline-secondary">Volver a proveedores</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="card-title h6 mb-0">Materiales suministrados por {{ $proveedor->nombre }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Material</th>
                            <th>Tipo</th>
                            <th class="text-end">Precio (Q)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($precios as $precio)
                            <tr>
                                <td><code>{{ $precio->material->codigo }}</code></td>
                                <td>
                                    <strong>{{ $precio->material->nombre }}</strong>
                                    <div class="small text-muted">{{ $precio->material->categoria }}</div>
                                </td>
                                <td>
                                    @if($precio->material->en_bodega)
                                        <span class="badge bg-primary">En bodega</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Solo cotizable</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace">Q{{ number_format($precio->precio, 2) }}</td>
                                <td class="text-end">
                                    <form action="{{ route('proveedores.catalogo.eliminar', [$proveedor, $precio]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Quitar del catálogo de este proveedor?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Quitar">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Este proveedor aún no tiene materiales asignados en su catálogo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Vincular material existente</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('proveedores.catalogo.guardar', $proveedor) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Material</label>
                        <select name="material_id" class="form-select form-select-sm" required>
                            <option value="">— Seleccionar material —</option>
                            @foreach($materialesDisponibles as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->nombre }} ({{ $mat->codigo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Precio de compra (Q)</label>
                        <input type="number" step="0.01" min="0" name="precio" class="form-control form-control-sm" required placeholder="0.00">
                    </div>
                    <button class="btn btn-sm btn-primary w-100">Agregar al catálogo</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="mb-0">Crear nuevo material "Solo cotizable"</h6>
                <small class="text-muted">No genera stock ni alertas de bodega</small>
            </div>
            <div class="card-body">
                <form action="{{ route('proveedores.catalogo.crear', $proveedor) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Código manual (opcional)</label>
                        <input type="text" name="codigo" class="form-control form-control-sm" placeholder="Dejar vacío para automático">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nombre del material</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required placeholder="Ej. Tubo PVC 2 pulg Durman">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Categoría</label>
                            <input type="text" name="categoria" class="form-control form-control-sm" required placeholder="Plomería">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Unidad</label>
                            <input type="text" name="unidad_medida" class="form-control form-control-sm" required placeholder="unidad/metro">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Precio de compra (Q)</label>
                        <input type="number" step="0.01" min="0" name="precio" class="form-control form-control-sm" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Descripción (opcional)</label>
                        <textarea name="descripcion" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <button class="btn btn-sm btn-success w-100">Crear y agregar a {{ $proveedor->nombre }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection