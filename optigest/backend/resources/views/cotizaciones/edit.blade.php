
@extends('layouts.app')
@section('titulo', 'Editar cotización')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">

<style>
    .ts-dropdown { z-index: 1055 !important; }
    .ts-control { border-radius: 0.375rem; min-height: 31px; font-size: 0.875rem; }
</style>

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Editar cotización</h2>
        <p class="text-muted small mb-0">
            Código: {{ $cotizacion->codigo }}
        </p>
    </div>
    <a href="{{ route('cotizaciones.show', $cotizacion) }}"
       class="btn btn-outline-secondary btn-sm">
        Cancelar
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudieron guardar los cambios:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('cotizaciones.update', $cotizacion) }}"
      class="bg-white p-4 rounded shadow-sm"
      style="max-width: 760px;"
      id="form-cotizacion">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label small">Cliente</label>
        <div class="d-flex gap-2">
            <select name="cliente_id" id="select-cliente" class="form-select" required>
                <option value="">— Selecciona un cliente —</option>
                @foreach ($clientes as $cli)
                    <option value="{{ $cli->id }}"
                        @selected(old('cliente_id', $cotizacion->cliente_id) == $cli->id)>
                        {{ $cli->nombre }}{{ $cli->telefono ? ' — '.$cli->telefono : '' }}
                    </option>
                @endforeach
            </select>

            <a href="{{ route('clientes.create', ['origen' => 'cotizacion']) }}"
               target="_blank"
               class="btn btn-outline-secondary text-nowrap">
                Cliente nuevo
            </a>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label small">Ticket relacionado (opcional)</label>
        <select name="ticket_id" class="form-select">
            <option value="">— Ninguno —</option>
            @foreach ($tickets as $tck)
                <option value="{{ $tck->id }}"
                    @selected(old('ticket_id', $cotizacion->ticket_id) == $tck->id)>
                    {{ $tck->codigo }} — {{ $tck->cliente->nombre ?? 'Sin cliente' }}
                </option>
            @endforeach
        </select>
    </div>

    <label class="form-label small fw-bold">Materiales</label>

    <div id="lineas-materiales">
        @php
            $materialesGuardados = old('materiales');

            if ($materialesGuardados === null) {
                $materialesGuardados = $cotizacion->detalles->map(function ($detalle) {
                    return [
                        'id' => $detalle->material_id,
                        'cantidad' => $detalle->cantidad,
                    ];
                })->values()->all();
            }

            if (empty($materialesGuardados)) {
                $materialesGuardados = [['id' => '', 'cantidad' => 1]];
            }
        @endphp

        @foreach ($materialesGuardados as $i => $linea)
            <div class="row g-2 mb-2 linea-material align-items-start border-bottom pb-2">
                <div class="col-8">
                    <select name="materiales[{{ $i }}][id]"
                            class="form-select form-select-sm select-material">
                        <option value="">— Buscar o seleccionar material —</option>

                        <optgroup label="📦 Existencias en Bodega">
                            @foreach ($materialesBodega as $mBod)
                                <option value="{{ $mBod['id'] }}"
                                    data-en-bodega="1"
                                    data-proveedor="{{ $mBod['proveedor'] }}"
                                    data-precio="{{ $mBod['precio_prov'] }}"
                                    @selected(($linea['id'] ?? '') == $mBod['id'])>
                                    {{ $mBod['codigo'] }} — {{ $mBod['nombre'] }}
                                    (Stock: {{ $mBod['stock'] }}) —
                                    Q{{ number_format($mBod['precio'], 2) }}
                                </option>
                            @endforeach
                        </optgroup>

                        @if (!empty($materialesExternos))
                            <optgroup label="🏭 Catálogo de Proveedores (Solo cotizable)">
                                @foreach ($materialesExternos as $mExt)
                                    <option value="{{ $mExt['id'] }}"
                                        data-en-bodega="0"
                                        data-proveedor="{{ $mExt['proveedor'] }}"
                                        data-precio="{{ $mExt['precio_prov'] }}"
                                        @selected(($linea['id'] ?? '') == $mExt['id'])>
                                        {{ $mExt['codigo'] }} — {{ $mExt['nombre'] }}
                                        (Solo cotizable) —
                                        Q{{ number_format($mExt['precio'], 2) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <div class="box-comparativa mt-1 small"></div>
                </div>

                <div class="col-3">
                    <input type="number"
                           name="materiales[{{ $i }}][cantidad]"
                           min="1"
                           step="1"
                           value="{{ $linea['cantidad'] ?? 1 }}"
                           class="form-control form-control-sm"
                           placeholder="Cantidad">
                </div>

                <div class="col-1 text-end">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-linea"
                            title="Quitar">&times;</button>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button"
            class="btn btn-sm btn-outline-secondary mb-3"
            id="agregar-linea">
        <i class="bi bi-plus-lg"></i> Agregar material
    </button>

    <label class="form-label small d-block mt-3 fw-bold">
        Mano de obra (precio estandarizado del catálogo)
    </label>

    <div id="lineas-servicios">
        @php
            $serviciosGuardados = old('servicios');

            if ($serviciosGuardados === null) {
                $serviciosGuardados = $cotizacion->servicios->map(function ($servicio) {
                    return [
                        'id' => $servicio->servicio_id,
                        'cantidad' => $servicio->cantidad,
                    ];
                })->values()->all();
            }

            if (empty($serviciosGuardados)) {
                $serviciosGuardados = [['id' => '', 'cantidad' => 1]];
            }
        @endphp

        @foreach ($serviciosGuardados as $i => $linea)
            <div class="row g-2 mb-2 linea-servicio align-items-start border-bottom pb-2">
                <div class="col-7">
                    <select name="servicios[{{ $i }}][id]"
                            class="form-select form-select-sm select-servicio">
                        <option value="">— Buscar o seleccionar servicio —</option>
                        @foreach ($servicios as $srv)
                            <option value="{{ $srv->id }}"
                                data-precio="{{ $srv->precio_estandar }}"
                                @selected(($linea['id'] ?? '') == $srv->id)>
                                {{ $srv->nombre }} —
                                Q{{ number_format($srv->precio_estandar, 2) }} /
                                {{ $srv->unidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-3">
                    <input type="number"
                           name="servicios[{{ $i }}][cantidad]"
                           min="0.01"
                           step="0.01"
                           value="{{ $linea['cantidad'] ?? 1 }}"
                           class="form-control form-control-sm"
                           placeholder="Cantidad">
                </div>

                <div class="col-2 text-end">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-servicio"
                            title="Quitar">&times;</button>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button"
            class="btn btn-sm btn-outline-secondary mb-2"
            id="agregar-servicio">
        <i class="bi bi-plus-lg"></i> Agregar servicio
    </button>

    @if ($servicios->isEmpty())
        <div class="form-text mb-2 text-warning">
            El catálogo de servicios está vacío. Puedes cargarlo en Catálogo → Servicios.
        </div>
    @endif

    @if ($cotizacion->ticket_id)
        <div class="alert alert-info small py-2 mt-3">
            Los gastos adicionales cobrables asociados al ticket se incluyen
            automáticamente en el total. Si cambias el ticket, se recalcularán
            los gastos asociados según el funcionamiento del sistema.
        </div>
    @else
        <div class="alert alert-info small py-2 mt-3">
            Si eliges un ticket, los gastos adicionales cobrables registrados
            en él podrán sumarse al total de esta cotización.
        </div>
    @endif

    <div class="form-check mb-3">
        <input class="form-check-input"
               type="checkbox"
               name="aplicar_iva"
               value="1"
               id="aplicar_iva"
               @checked(old('aplicar_iva', $cotizacion->iva_aplicado))>

        <label class="form-check-label small" for="aplicar_iva">
            Aplicar IVA
            ({{ rtrim(rtrim(number_format(($ivaTasa ?? 0.12) * 100, 2), '0'), '.') }} %)
        </label>
    </div>

    <div class="mb-3">
        <label class="form-label small">Observaciones</label>
        <textarea name="observaciones"
                  class="form-control"
                  rows="2"
                  placeholder="Notas sobre entrega, validez de la oferta, etc...">{{ old('observaciones', $cotizacion->observaciones) }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        Guardar cambios
    </button>

    <a href="{{ route('cotizaciones.show', $cotizacion) }}"
       class="btn btn-outline-secondary">
        Cancelar
    </a>
</form>

<template id="tpl-material">
    <div class="row g-2 mb-2 linea-material align-items-start border-bottom pb-2">
        <div class="col-8">
            <select name="materiales[INDEX][id]" class="form-select form-select-sm select-material">
                <option value="">— Buscar o seleccionar material —</option>
                <optgroup label="📦 Existencias en Bodega">
                    @foreach ($materialesBodega as $mBod)
                        <option value="{{ $mBod['id'] }}"
                                data-en-bodega="1"
                                data-proveedor="{{ $mBod['proveedor'] }}"
                                data-precio="{{ $mBod['precio_prov'] }}">
                            {{ $mBod['codigo'] }} — {{ $mBod['nombre'] }}
                            (Stock: {{ $mBod['stock'] }}) —
                            Q{{ number_format($mBod['precio'], 2) }}
                        </option>
                    @endforeach
                </optgroup>

                @if (!empty($materialesExternos))
                    <optgroup label="🏭 Catálogo de Proveedores (Solo cotizable)">
                        @foreach ($materialesExternos as $mExt)
                            <option value="{{ $mExt['id'] }}"
                                    data-en-bodega="0"
                                    data-proveedor="{{ $mExt['proveedor'] }}"
                                    data-precio="{{ $mExt['precio_prov'] }}">
                                {{ $mExt['codigo'] }} — {{ $mExt['nombre'] }}
                                (Solo cotizable) —
                                Q{{ number_format($mExt['precio'], 2) }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
            <div class="box-comparativa mt-1 small"></div>
        </div>

        <div class="col-3">
            <input type="number" name="materiales[INDEX][cantidad]"
                   min="1" step="1" value="1"
                   class="form-control form-control-sm" placeholder="Cantidad">
        </div>

        <div class="col-1 text-end">
            <button type="button"
                    class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-linea"
                    title="Quitar">&times;</button>
        </div>
    </div>
</template>

<template id="tpl-servicio">
    <div class="row g-2 mb-2 linea-servicio align-items-start border-bottom pb-2">
        <div class="col-7">
            <select name="servicios[INDEX][id]" class="form-select form-select-sm select-servicio">
                <option value="">— Buscar o seleccionar servicio —</option>
                @foreach ($servicios as $srv)
                    <option value="{{ $srv->id }}" data-precio="{{ $srv->precio_estandar }}">
                        {{ $srv->nombre }} — Q{{ number_format($srv->precio_estandar, 2) }} / {{ $srv->unidad }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-3">
            <input type="number" name="servicios[INDEX][cantidad]"
                   min="0.01" step="0.01" value="1"
                   class="form-control form-control-sm" placeholder="Cantidad">
        </div>

        <div class="col-2 text-end">
            <button type="button"
                    class="btn btn-sm btn-outline-danger py-0 px-2 btn-eliminar-servicio"
                    title="Quitar">&times;</button>
        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const configTomSelect = {
        create: false,
        maxOptions: 150,
        placeholder: 'Escribe para buscar...',
        allowEmptyOption: true,
        plugins: ['dropdown_input']
    };

    const selectCliente = document.getElementById('select-cliente');
    if (selectCliente) new TomSelect(selectCliente, configTomSelect);

    function inicializarBuscadorMaterial(select) {
        if (!select || select.tomselect) return select?.tomselect;

        return new TomSelect(select, {
            ...configTomSelect,
            onChange: function () {
                actualizarSugerencia(select);
            }
        });
    }

    function inicializarBuscadorServicio(select) {
        if (!select || select.tomselect) return select?.tomselect;
        return new TomSelect(select, configTomSelect);
    }

    function escapeHtml(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[c]));
    }

    function actualizarSugerencia(select) {
        const fila = select.closest('.linea-material');
        if (!fila) return;

        const contenedor = fila.querySelector('.box-comparativa');
        const opcion = select.options[select.selectedIndex];

        if (!opcion || !opcion.value) {
            contenedor.innerHTML = '';
            return;
        }

        const enBodega = opcion.dataset.enBodega === '1';
        const proveedor = opcion.dataset.proveedor;
        const precio = opcion.dataset.precio;

        let html = enBodega
            ? '<span class="badge bg-primary me-1">En bodega</span> '
            : '<span class="badge bg-warning text-dark me-1">Proveedor externo</span> ';

        if (proveedor && precio) {
            html += `<span class="text-success fw-bold">Más económico: ${escapeHtml(proveedor)} (Q${(parseFloat(precio) || 0).toFixed(2)})</span>`;
        } else {
            html += '<span class="text-muted fst-italic">Sin comparativa disponible</span>';
        }

        contenedor.innerHTML = html;
    }

    document.querySelectorAll('.select-material').forEach(select => {
        inicializarBuscadorMaterial(select);
        actualizarSugerencia(select);
    });

    document.querySelectorAll('.select-servicio').forEach(inicializarBuscadorServicio);

    const contenedorMateriales = document.getElementById('lineas-materiales');
    const tplMaterial = document.getElementById('tpl-material');
    let contadorMateriales = Math.max(
        -1,
        ...Array.from(document.querySelectorAll('[name^="materiales["]'))
            .map(el => {
                const match = el.name.match(/^materiales\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            })
    ) + 1;

    document.getElementById('agregar-linea').addEventListener('click', function () {
        const fragmento = tplMaterial.content.cloneNode(true);
        const fila = fragmento.querySelector('.linea-material');

        fila.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace('INDEX', contadorMateriales);
        });

        contenedorMateriales.appendChild(fila);
        inicializarBuscadorMaterial(fila.querySelector('.select-material'));
        contadorMateriales++;
    });

    const contenedorServicios = document.getElementById('lineas-servicios');
    const tplServicio = document.getElementById('tpl-servicio');
    let contadorServicios = Math.max(
        -1,
        ...Array.from(document.querySelectorAll('[name^="servicios["]'))
            .map(el => {
                const match = el.name.match(/^servicios\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            })
    ) + 1;

    document.getElementById('agregar-servicio').addEventListener('click', function () {
        const fragmento = tplServicio.content.cloneNode(true);
        const fila = fragmento.querySelector('.linea-servicio');

        fila.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace('INDEX', contadorServicios);
        });

        contenedorServicios.appendChild(fila);
        inicializarBuscadorServicio(fila.querySelector('.select-servicio'));
        contadorServicios++;
    });

    document.addEventListener('click', function (e) {
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
