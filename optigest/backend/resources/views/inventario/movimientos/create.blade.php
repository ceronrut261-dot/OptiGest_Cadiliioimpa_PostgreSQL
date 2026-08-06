@extends('layouts.app')

@section('titulo', 'Nuevo movimiento')

@section('contenido')
<h2 class="h4 mb-4">Registrar movimiento de inventario</h2>

<form method="POST" action="{{ route('inventario.movimientos.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Material</label>
        <select name="material_id" class="form-select" required>
            <option value="">— Selecciona —</option>
            @foreach($materiales as $material)
                <option value="{{ $material->id }}">{{ $material->codigo }} — {{ $material->nombre }} (stock: {{ $material->stock }})</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small">Tipo de movimiento</label>
        <select name="tipo" class="form-select" required>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small">Cantidad</label>
        <input type="number" name="cantidad" min="1" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Motivo (opcional)</label>
        <input type="text" name="motivo" class="form-control" placeholder="Ej. compra a proveedor, uso en servicio, ajuste">
    </div>

    <button type="submit" class="btn btn-primary">Registrar movimiento</button>
    <a href="{{ route('inventario.movimientos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
