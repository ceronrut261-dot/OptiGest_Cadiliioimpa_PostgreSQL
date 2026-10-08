@extends('layouts.app')

@section('titulo', 'Cotizar a ' . $proveedor->nombre)

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-0">Solicitud de Cotización</h2>
        <span class="text-muted small">Proveedor: <strong>{{ $proveedor->nombre }}</strong> &bull; Contacto: {{ $proveedor->nombre_contacto ?? $proveedor->telefono ?? 'Sin contacto' }}</span>
    </div>
    <a href="{{ route('proveedores.catalogo', $proveedor) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Catálogo
    </a>
</div>

@if(session('status'))
    <div class="alert alert-warning">{{ session('status') }}</div>
@endif

<form action="{{ route('proveedores.solicitud.pdf', $proveedor) }}" method="POST" target="_blank">
    @csrf

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-5">
                    <h6 class="fw-bold mb-0 text-primary">Insumos disponibles para cotizar</h6>
                </div>
                {{-- Barra de búsqueda en tiempo real --}}
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" 
                               id="buscadorMateriales" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Filtrar por código o nombre del material...">
                        <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltro" title="Limpiar filtro">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaInsumos">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">Código</th>
                        <th style="width: 35%">Material</th>
                        <th style="width: 15%">Precio Registrado</th>
                        <th style="width: 15%">Cantidad a Cotizar</th>
                        <th style="width: 20%" class="text-end">Subtotal Estimado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($precios as $item)
                        @php
                            $mat = $item->material;
                            $precioUnit = (float)$item->precio;
                        @endphp
                        <tr class="fila-material">
                            <td class="col-codigo">
                                <code>{{ $mat->codigo }}</code>
                                <input type="hidden" name="items[{{ $loop->index }}][codigo]" value="{{ $mat->codigo }}">
                                <input type="hidden" name="items[{{ $loop->index }}][nombre]" value="{{ $mat->nombre }}">
                                <input type="hidden" name="items[{{ $loop->index }}][unidad]" value="{{ $mat->unidad_medida }}">
                                <input type="hidden" name="items[{{ $loop->index }}][precio]" value="{{ $precioUnit }}">
                            </td>
                            <td class="col-nombre">
                                <div class="fw-bold text-dark">{{ $mat->nombre }}</div>
                                <small class="text-muted">{{ $mat->categoria }}</small>
                            </td>
                            <td>
                                <span class="font-monospace">Q{{ number_format($precioUnit, 2) }}</span>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           name="items[{{ $loop->index }}][cantidad]" 
                                           class="form-control input-cantidad text-center" 
                                           placeholder="0"
                                           data-precio="{{ $precioUnit }}">
                                    <span class="input-group-text">{{ $mat->unidad_medida }}</span>
                                </div>
                            </td>
                            <td class="text-end font-monospace fw-bold subtotal-celda">
                                Q0.00
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Este proveedor no tiene materiales vinculados en su catálogo.
                            </td>
                        </tr>
                    @endforelse
                    {{-- Fila que se muestra si la búsqueda no encuentra nada --}}
                    <tr id="sinCoincidencias" style="display: none;">
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-search me-1"></i> No se encontraron materiales que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
                @if(count($precios) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Gran Total Estimado:</td>
                            <td class="text-end font-monospace fw-bold text-success fs-5" id="granTotal">
                                Q0.00
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if(count($precios) > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="observaciones" class="form-label fw-bold">Instrucciones o notas adicionales para el proveedor</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Ej. Indicar tiempo de entrega, costo de flete, validez de la oferta..."></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Generar Solicitud de Cotización (PDF)
                    </button>
                </div>
            </div>
        </div>
    @endif
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputsCantidad = document.querySelectorAll('.input-cantidad');
    const labelGranTotal = document.getElementById('granTotal');
    const buscador = document.getElementById('buscadorMateriales');
    const btnLimpiar = document.getElementById('btnLimpiarFiltro');
    const filas = document.querySelectorAll('.fila-material');
    const filaSinCoincidencias = document.getElementById('sinCoincidencias');

    // 1. Cálculo dinámico de subtotales y total general
    function actualizarTotales() {
        let totalGeneral = 0;

        inputsCantidad.forEach(input => {
            const precio = parseFloat(input.dataset.precio) || 0;
            const cantidad = parseFloat(input.value) || 0;
            const subtotal = precio * cantidad;

            const fila = input.closest('tr');
            const celdaSubtotal = fila.querySelector('.subtotal-celda');
            if (celdaSubtotal) {
                celdaSubtotal.textContent = 'Q' + subtotal.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            totalGeneral += subtotal;
        });

        if (labelGranTotal) {
            labelGranTotal.textContent = 'Q' + totalGeneral.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    inputsCantidad.forEach(input => {
        input.addEventListener('input', actualizarTotales);
    });

    // 2. Filtro en tiempo real por Código o Nombre
    function filtrarTabla() {
        const texto = buscador.value.toLowerCase().trim();
        let visibles = 0;

        filas.forEach(fila => {
            const codigo = fila.querySelector('.col-codigo').textContent.toLowerCase();
            const nombre = fila.querySelector('.col-nombre').textContent.toLowerCase();

            if (codigo.includes(texto) || nombre.includes(texto)) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        if (filaSinCoincidencias) {
            filaSinCoincidencias.style.display = visibles === 0 ? '' : 'none';
        }
    }

    if (buscador) {
        buscador.addEventListener('input', filtrarTabla);
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            buscador.value = '';
            filtrarTabla();
            buscador.focus();
        });
    }
});
</script>
@endsection