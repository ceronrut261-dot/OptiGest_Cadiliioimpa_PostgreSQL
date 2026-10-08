@extends('layouts.app')
@section('titulo', 'Nueva cotización')

{{-- Estilos de TomSelect compatibles con Bootstrap 5 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-dropdown { z-index: 1055 !important; }
    .ts-control { border-radius: 0.375rem; min-height: 31px; font-size: 0.875rem; }
</style>

@section('contenido')
<h2 class="h4 mb-4">Nueva cotización</h2>

<form method="POST" action="{{ route('cotizaciones.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:760px;" id="form-cotizacion">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Cliente</label>
        <div class="d-flex gap-2">
            <select name="cliente_id" id="select-cliente" class="form-select" required>
                <option value="">— Selecciona un cliente —</option>
                @foreach ($clientes as$cliente)
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
            @foreach ($tickets as$ticket)
                <option value="{{ $ticket->id }}">{{ $ticket->codigo }} — {{$ticket->cliente->nombre }}</option>
            @endforeach
        </select>
    </div>

    <label class="form-label small fw-bold">Materiales</label>
    <div id="lineas-materiales">
        <div class="row g-2 mb-2 linea-material align-items-start border-bottom pb-2">
            <div class="col-8">
                <select name="materiales[0][id]" class="form-select form-select-sm select-material">
                    <option value="">— Buscar o seleccionar material —</option>
                    @foreach ($materiales as$material)
                        @php
                            $comp =$material->comparativaProveedores();
                            $enBodega =$material->en_bodega ? '1' : '0';
                            $proveedor =$comp['mas_barato']['proveedor'] ?? '';
                            $precioProv =$comp['mas_barato']['precio'] ?? '';
                        @endphp
                        <option value="{{ $material->id }}" 
                                data-en-bodega="{{ $enBodega }}" 
                                data-proveedor="{{ $proveedor }}" 
                                data-precio="{{ $precioProv }}">
                            {{ $material->codigo }} — {{ $material->nombre }} ({{$material->en_bodega ? 'Stock: '.$material->stock : 'Solo cotizable' }}) — Q{{ number_format($material->precio, 2) }}
                        </option>
                    @endforeach
                </select>
                <div class="box-comparativa mt-1 small"></div>
            </div>
            <div class="col-3">
                <input type="number" name="materiales[0][cantidad]" min="1" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-linea" title="Quitar">&times;</button>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="agregar-linea">
        <i class="bi bi-plus-lg"></i> Agregar material
    </button>

    <label class="form-label small d-block mt-3 fw-bold">Mano de obra (precio estandarizado del catálogo)</label>
    <div id="lineas-servicios">
        <div class="row g-2 mb-2 linea-servicio align-items-start border-bottom pb-2">
            <div class="col-7">
                <select name="servicios[0][id]" class="form-select form-select-sm select-servicio">
                    <option value="">— Buscar o seleccionar servicio —</option>
                    @foreach ($servicios as$servicio)
                        <option value="{{ $servicio->id }}" data-precio="{{ $servicio->precio_estandar }}">
                            {{ $servicio->nombre }} — Q{{ number_format($servicio->precio_estandar, 2) }} / {{$servicio->unidad }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="servicios[0][cantidad]" min="0.01" step="0.01" value="1" class="form-control form-control-sm" placeholder="Cantidad">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-servicio" title="Quitar">&times;</button>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="agregar-servicio">
        <i class="bi bi-plus-lg"></i> Agregar servicio
    </button>

    @if ($servicios->isEmpty())
        <div class="form-text mb-2 text-warning">
            <i class="bi bi-exclamation-triangle"></i> El catálogo de servicios está vacío. Puedes cargarlo en Catálogo → Servicios.
        </div>
    @endif

    <div class="alert alert-info small py-2 mt-3">
        Si eliges un ticket, los <strong>gastos adicionales cobrables</strong> registrados en él se suman automáticamente al total de esta cotización.
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="aplicar_iva" value="1" id="aplicar_iva">
        <label class="form-check-label small" for="aplicar_iva">Aplicar IVA ({{ rtrim(rtrim(number_format($ivaTasa * 100, 2), '0'), '.') }} %)</label>
    </div>

    <div class="mb-3">
        <label class="form-label small">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas sobre entrega, validez de la oferta, etc..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Crear cotización (borrador)</button>
    <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>

{{-- Script de TomSelect --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Configuración base de búsqueda para TomSelect
    const configTomSelect = {
        create: false,
        maxOptions: 100,
        placeholder: 'Escribe para buscar...',
        allowEmptyOption: true,
        plugins: ['dropdown_input'],
    };

    // Buscador en cliente
    const selectCliente = document.getElementById('select-cliente');
    if (selectCliente) {
        new TomSelect(selectCliente, configTomSelect);
    }

    function inicializarBuscadorMaterial(select) {
        if (select.tomselect) return select.tomselect;

        const ts = new TomSelect(select, {
            ...configTomSelect,
            onChange: function() {
                actualizarSugerencia(select);
            }
        });
        return ts;
    }

    function inicializarBuscadorServicio(select) {
        if (select.tomselect) return select.tomselect;
        return new TomSelect(select, configTomSelect);
    }

    function actualizarSugerencia(select) {
        const contenedor = select.closest('.linea-material').querySelector('.box-comparativa');
        const opcion = select.options[select.selectedIndex];
        
        if (!opcion || !opcion.value) {
            contenedor.innerHTML = '';
            return;
        }

        const enBodega = opcion.dataset.enBodega === '1';
        const proveedor = opcion.dataset.proveedor;
        const precio = opcion.dataset.precio;

        let html = '';
        if (!enBodega) {
            html += '<span class="badge bg-warning text-dark me-1"><i class="bi bi-cart-plus"></i> Comprar a proveedor</span> ';
        } else {
            html += '<span class="badge bg-primary me-1">En bodega</span> ';
        }

        if (proveedor && precio) {
            html += `<span class="text-success fw-bold">Más económico: ${proveedor} (Q${parseFloat(precio).toFixed(2)})</span>`;
        } else {
            html += '<span class="text-muted fst-italic">Sin precios de compra registrados</span>';
        }

        contenedor.innerHTML = html;
    }

    // Inicializar los que ya vienen al cargar la página
    document.querySelectorAll('.select-material').forEach(sel => {
        inicializarBuscadorMaterial(sel);
        actualizarSugerencia(sel);
    });

    document.querySelectorAll('.select-servicio').forEach(sel => {
        inicializarBuscadorServicio(sel);
    });

    // Guardamos plantillas limpias HTML antes de que se alteren
    const contenedorMateriales = document.getElementById('lineas-materiales');
    const plantillaMaterial = contenedorMateriales.querySelector('.linea-material').cloneNode(true);
    plantillaMaterial.querySelectorAll('.ts-wrapper').forEach(el => el.remove());
    plantillaMaterial.querySelector('.select-material').style.display = '';

    const contenedorServicios = document.getElementById('lineas-servicios');
    const plantillaServicio = contenedorServicios.querySelector('.linea-servicio').cloneNode(true);
    plantillaServicio.querySelectorAll('.ts-wrapper').forEach(el => el.remove());
    plantillaServicio.querySelector('.select-servicio').style.display = '';

    // Agregar nueva fila de material
    let contadorMateriales = 1;
    document.getElementById('agregar-linea').addEventListener('click', function () {
        const nuevaFila = plantillaMaterial.cloneNode(true);

        nuevaFila.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${contadorMateriales}]`);
            if (el.tagName === 'INPUT') el.value = 1;
            if (el.tagName === 'SELECT') el.value = '';
        });

        nuevaFila.querySelector('.box-comparativa').innerHTML = '';
        contenedorMateriales.appendChild(nuevaFila);

        const selectNuevo = nuevaFila.querySelector('.select-material');
        inicializarBuscadorMaterial(selectNuevo);
        contadorMateriales++;
    });

    // Agregar nueva fila de servicio
    let contadorServicios = 1;
    document.getElementById('agregar-servicio').addEventListener('click', function () {
        const nuevaFila = plantillaServicio.cloneNode(true);

        nuevaFila.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${contadorServicios}]`);
            if (el.tagName === 'INPUT') el.value = 1;
            if (el.tagName === 'SELECT') el.value = '';
        });

        contenedorServicios.appendChild(nuevaFila);

        const selectNuevo = nuevaFila.querySelector('.select-servicio');
        inicializarBuscadorServicio(selectNuevo);
        contadorServicios++;
    });

    // Botones para quitar filas sobrantes
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-eliminar-linea')) {
            const filas = document.querySelectorAll('.linea-material');
            if (filas.length > 1) {
                e.target.closest('.linea-material').remove();
            } else {
                alert('Debe quedar al menos una fila de material.');
            }
        }

        if (e.target.closest('.btn-eliminar-servicio')) {
            const filas = document.querySelectorAll('.linea-servicio');
            if (filas.length > 1) {
                e.target.closest('.linea-servicio').remove();
            } else {
                alert('Debe quedar al menos una fila de servicio.');
            }
        }
    });
});
</script>
@endsection