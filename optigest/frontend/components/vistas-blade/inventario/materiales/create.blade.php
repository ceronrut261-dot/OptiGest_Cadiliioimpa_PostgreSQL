@extends('layouts.app')

@section('titulo', isset($material) ? 'Editar material' : 'Nuevo material')

@section('contenido')
<h2 class="h4 mb-4">{{ isset($material) ? 'Editar material' : 'Nuevo material' }}</h2>

<form method="POST" action="{{ isset($material) ? route('inventario.materiales.update', $material) : route('inventario.materiales.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    @csrf
    @if(isset($material)) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label small">Nombre</label>
        <input type="text" name="nombre" value="{{ old('nombre', $material->nombre ?? '') }}" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Categoría</label>
            <input type="text" name="categoria" value="{{ old('categoria', $material->categoria ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Unidad de medida</label>
            <input type="text" name="unidad_medida" value="{{ old('unidad_medida', $material->unidad_medida ?? 'unidad') }}" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $material->descripcion ?? '') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label small">Precio (Q)</label>
            <input type="number" step="0.01" name="precio" value="{{ old('precio', $material->precio ?? '') }}" class="form-control" required>
        </div>
        @unless(isset($material))
        <div class="col-md-4 mb-3">
            <label class="form-label small">Stock inicial</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" class="form-control">
        </div>
        @endunless
        <div class="col-md-4 mb-3">
            <label class="form-label small">Stock mínimo (alerta)</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $material->stock_minimo ?? 10) }}" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Proveedor</label>
        <select name="proveedor_id" class="form-select">
            <option value="">— Sin proveedor —</option>
            @foreach($proveedores as $proveedor)
                <option value="{{ $proveedor->id }}" {{ old('proveedor_id', $material->proveedor_id ?? '') == $proveedor->id ? 'selected' : '' }}>
                    {{ $proveedor->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('inventario.materiales.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
