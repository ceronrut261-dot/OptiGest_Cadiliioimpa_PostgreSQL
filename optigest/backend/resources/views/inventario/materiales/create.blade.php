@extends('layouts.app')

@section('titulo', 'Nuevo material')

@section('contenido')
<h2 class="h4 mb-4">Nuevo material</h2>

<form method="POST" action="{{ route('inventario.materiales.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    @csrf

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label small">Código (opcional)</label>
            <input type="text" name="codigo" value="{{ old('codigo') }}" class="form-control @error('codigo') is-invalid @enderror" placeholder="Dejar vacío para auto">
            @error('codigo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label small">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Categoría</label>
            <input type="text" name="categoria" value="{{ old('categoria') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Unidad de medida</label>
            <input type="text" name="unidad_medida" value="{{ old('unidad_medida', 'unidad') }}" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="en_bodega" value="1" id="en_bodega" {{ old('en_bodega', true) ? 'checked' : '' }}>
        <label class="form-check-label fw-bold small" for="en_bodega">
            Se mantiene en bodega
        </label>
        <div class="form-text">Desmarcar si es solo cotizable (no generará stock, salidas de bodega ni alertas).</div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label small">Precio (Q)</label>
            <input type="number" step="0.01" name="precio" value="{{ old('precio') }}" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3" id="campo-stock">
            <label class="form-label small">Stock inicial</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" class="form-control">
        </div>
        <div class="col-md-4 mb-3" id="campo-stock-minimo">
            <label class="form-label small">Stock mínimo (alerta)</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 10) }}" class="form-control">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Proveedor principal</label>
        <select name="proveedor_id" class="form-select">
            <option value="">— Sin proveedor —</option>
            @foreach($proveedores as $proveedor)
                <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                    {{ $proveedor->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('inventario.materiales.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>

<script>
const switchBodega = document.getElementById('en_bodega');
const campoStock = document.getElementById('campo-stock');
const campoMinimo = document.getElementById('campo-stock-minimo');

function toggleBodega() {
    if (switchBodega.checked) {
        campoStock.style.display = 'block';
        campoMinimo.style.display = 'block';
    } else {
        campoStock.style.display = 'none';
        campoMinimo.style.display = 'none';
    }
}
switchBodega.addEventListener('change', toggleBodega);
toggleBodega();
</script>
@endsection