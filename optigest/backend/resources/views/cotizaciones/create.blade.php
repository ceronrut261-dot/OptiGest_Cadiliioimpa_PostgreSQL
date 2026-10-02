@extends('layouts.app')
@section('titulo', 'Nueva cotización')
@section('contenido')
<h2 class="h4 mb-4">Nueva cotización</h2>

<form method="POST" action="{{ route('cotizaciones.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:760px;" id="form-cotizacion">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Cliente</label>
        <div class="d-flex gap-2">
            <select name="cliente_id" class="form-select" required>
                <option value="">— Selecciona un cliente —</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}{{ $cliente->telefono ? ' — '.$cliente->telefono : '' }}</option>
                @endforeach
            </select>
            <a href="{{ route('clientes.create', ['origen' => 'cotizacion']) }}" target="_blank" class="btn btn-outline-secondary text-nowrap">Cliente nuevo</a>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Ticket relacionado (opcional)</label>
        <select name="ticket_id" class="form-select">
            <option value="">— Ninguno —</option>
            @foreach($tickets as $ticket)
                <option value="{{ $ticket->id }}">{{ $ticket->codigo }} — {{ $ticket->cliente->nombre }}</option>
            @endforeach
        </select>
    </div>

    <label class="form-label small">Materiales</label>
    <div id="lineas-materiales">
        <div class="row g-2 mb-1 linea-material align-items-start">
            <div class="col-7">
                <select name="materiales[0][id]" class="form-select form-select-sm select-material">
                    <option value="">— Sin material —</option>
                    @foreach($materiales as $material)
                        <option value="{{ $material->id }}" data-mejor="{{ $mejoresProveedores[$material->id]['proveedor'] ?? '' }}" data-mejor-precio="{{ $mejoresProveedores[$material->id]['precio'] ?? '' }}">
                            {{ $material->nombre }} (stock: {{ $material->stock }}) — Q{{ number_format($material->precio,2) }}
                        </option>
                    @endforeach
                </select>
                <small class="text-success d-block mt-1 texto-mejor-proveedor"></small>
            </div>
            <div class="col-3">
                <input type="number" name="materiales[0][cantidad]" min="1" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="agregar-linea">+ Agregar material</button>

    <label class="form-label small d-block mt-3">Mano de obra (precio estandarizado del catálogo)</label>
    <div id="lineas-servicios">
        <div class="row g-2 mb-1 linea-servicio align-items-start">
            <div class="col-7">
                <select name="servicios[0][id]" class="form-select form-select-sm select-servicio">
                    <option value="">— Sin mano de obra —</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}" data-precio="{{ $servicio->precio_estandar }}">
                            {{ $servicio->nombre }} — Q{{ number_format($servicio->precio_estandar, 2) }} / {{ $servicio->unidad }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="servicios[0][cantidad]" min="0.01" step="0.01" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="agregar-servicio">+ Agregar servicio</button>
    @if($servicios->isEmpty())
        <div class="form-text mb-2">El catálogo de servicios está vacío. Un administrador puede cargarlo en Catálogo → Servicios.</div>
    @endif

    <div class="alert alert-info small py-2">
        Si eliges un ticket, los <strong>gastos adicionales cobrables</strong> registrados en él (compras de material, etc.)
        se suman automáticamente al total de esta cotización.
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="aplicar_iva" value="1" id="aplicar_iva">
        <label class="form-check-label small" for="aplicar_iva">Aplicar IVA ({{ rtrim(rtrim(number_format($ivaTasa * 100, 2), '0'), '.') }} %)</label>
    </div>

    <div class="mb-3"><label class="form-label small">Observaciones</label><textarea name="observaciones" class="form-control" rows="2"></textarea></div>

    <button type="submit" class="btn btn-primary">Crear cotización (borrador)</button>
    <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>

<script>
function actualizarSugerencia(select) {
    const opcion = select.options[select.selectedIndex];
    const texto = select.closest('.linea-material').querySelector('.texto-mejor-proveedor');
    const proveedor = opcion.dataset.mejor;
    const precio = opcion.dataset.mejorPrecio;
    texto.textContent = proveedor
        ? `Proveedor más conveniente: ${proveedor} — Q${parseFloat(precio).toFixed(2)}`
        : '';
}

document.querySelectorAll('.select-material').forEach(sel => {
    actualizarSugerencia(sel);
    sel.addEventListener('change', () => actualizarSugerencia(sel));
});

let contadorServicios = 1;
document.getElementById('agregar-servicio').addEventListener('click', function () {
    const contenedor = document.getElementById('lineas-servicios');
    const clon = contenedor.querySelector('.linea-servicio').cloneNode(true);
    clon.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${contadorServicios}]`);
        if (el.tagName === 'INPUT') el.value = 1; else el.value = '';
    });
    contenedor.appendChild(clon);
    contadorServicios++;
});

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
    const nuevoSelect = clon.querySelector('.select-material');
    actualizarSugerencia(nuevoSelect);
    nuevoSelect.addEventListener('change', () => actualizarSugerencia(nuevoSelect));
    contador++;
});
</script>
@endsection