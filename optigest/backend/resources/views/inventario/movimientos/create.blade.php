@extends('layouts.app')

@section('titulo', 'Nuevo movimiento')

@section('contenido')
<h2 class="h4 mb-4">Registrar movimiento de inventario</h2>

<form method="POST" action="{{ route('inventario.movimientos.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Tipo de movimiento</label>
        <select name="tipo" id="tipo" class="form-select" required>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label small">Material</label>
        <select name="material_id" id="material_id" class="form-select" required>
            <option value="">— Selecciona —</option>
            @foreach($materiales as $material)
                <option value="{{ $material->id }}"
                        data-bajo-stock="{{ $material->stock <= $material->stock_minimo ? '1' : '0' }}">
                    {{ $material->codigo }} — {{ $material->nombre }} (stock: {{ $material->stock }})
                    @if($material->stock <= $material->stock_minimo) — BAJO STOCK @endif
                </option>
            @endforeach
        </select>
        <small id="aviso-bajo-stock" class="text-danger d-none">
            Este material ya está en nivel de stock bajo. No se puede registrar una salida hasta reabastecerlo — puedes registrar una <strong>entrada</strong> en su lugar.
        </small>
    </div>

    <div class="mb-3">
        <label class="form-label small">Cantidad</label>
        <input type="number" name="cantidad" min="1" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Motivo (opcional)</label>
        <input type="text" name="motivo" class="form-control" placeholder="Ej. compra a proveedor, uso en servicio, ajuste">
    </div>

    <button type="submit" class="btn btn-primary" id="btn-registrar">Registrar movimiento</button>
    <a href="{{ route('inventario.movimientos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>

<script>
const tipoSelect = document.getElementById('tipo');
const materialSelect = document.getElementById('material_id');
const aviso = document.getElementById('aviso-bajo-stock');
const btn = document.getElementById('btn-registrar');

function evaluarBloqueo() {
    const opcion = materialSelect.options[materialSelect.selectedIndex];
    const esBajoStock = opcion && opcion.dataset.bajoStock === '1';
    const esSalida = tipoSelect.value === 'salida';

    const bloquear = esBajoStock && esSalida;
    aviso.classList.toggle('d-none', !bloquear);
    btn.disabled = bloquear;
}

tipoSelect.addEventListener('change', evaluarBloqueo);
materialSelect.addEventListener('change', evaluarBloqueo);
evaluarBloqueo();
</script>
@endsection