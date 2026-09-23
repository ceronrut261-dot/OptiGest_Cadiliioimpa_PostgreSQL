@extends('layouts.app')
@section('titulo', 'Nueva salida de materiales')
@section('contenido')
<h2 class="h4 mb-4">Nueva salida de materiales</h2>

<form method="POST" action="{{ route('salidas.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:760px;" id="form-salida">
    @csrf

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label small">Nombre del proyecto</label>
            <input type="text" name="proyecto" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small">Persona que recibe</label>
            <input type="text" name="persona_recibe" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Ticket relacionado (opcional)</label>
        <select name="ticket_id" class="form-select">
            <option value="">— Ninguno —</option>
            @foreach($tickets as $ticket)
                <option value="{{ $ticket->id }}">{{ $ticket->codigo }}</option>
            @endforeach
        </select>
    </div>

    <label class="form-label small">Materiales a entregar</label>
    <div id="lineas-materiales">
        <div class="row g-2 mb-2 linea-material">
            <div class="col-7">
                <select name="materiales[0][id]" class="form-select form-select-sm">
                    @foreach($materiales as $material)
                        <option value="{{ $material->id }}">{{ $material->nombre }} (stock: {{ $material->stock }}) — Q{{ number_format($material->precio,2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="materiales[0][cantidad]" min="1" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="agregar-linea">+ Agregar material</button>

    <div class="mb-3">
        <label class="form-label small">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label small">¿Falta algo por comprar?</label>
        <textarea name="falta_comprar" class="form-control" rows="2" placeholder="Deja en blanco si no falta nada"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Registrar salida y generar vale</button>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>

<script>
let contador = 1;
document.getElementById('agregar-linea').addEventListener('click', function () {
    const contenedor = document.getElementById('lineas-materiales');
    const original = contenedor.querySelector('.linea-material');
    const clon = original.cloneNode(true);
    clon.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${contador}]`);
        if (el.tagName === 'INPUT') el.value = 1;
    });
    contenedor.appendChild(clon);
    contador++;
});
</script>
@endsection
