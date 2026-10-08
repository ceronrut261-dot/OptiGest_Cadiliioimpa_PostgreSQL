@extends('layouts.app')

@section('titulo', 'Solicitud de Cotización')

{{-- Estilos para selector de TomSelect --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-dropdown { z-index: 1055 !important; }
</style>

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-0">Solicitud de Cotización de Insumos</h2>
        <span class="text-muted small">Proveedor principal: <strong>{{ $proveedor->nombre }}</strong> &bull; Contacto: {{$proveedor->nombre_contacto ?? 'General' }}</span>
    </div>
    <a href="{{ route('cotizaciones.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Cotizaciones
    </a>
</div>

@if(session('status'))
    <div class="alert alert-warning">{{ session('status') }}</div>
@endif

<form action="{{ route('proveedores.solicitud.pdf', $proveedor) }}" method="POST" target="_blank" id="formSolicitud">
    @csrf

    {{-- Tarjeta para agregar materiales de OTROS proveedores si el actual no lo tiene --}}
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body py-3">
            <label class="form-label fw-bold text-dark small mb-1">
                <i class="bi bi-plus-circle me-1 text-primary"></i> ¿El proveedor no tiene un material? Búscalo y agrégalo desde otros proveedores:
            </label>
            <div class="row g-2 align-items-center">
                <div class="col-md-9">
                    <select id="selectorMaterialesGlobal" class="form-select form-select-sm" placeholder="Escribe el código o nombre del material a buscar en otros proveedores...">
                        <option value="">— Buscar material en cualquier proveedor —</option>
                        <?php if(isset($todosLosMateriales)): ?>
                            <?php foreach ($todosLosMateriales as $matGlobal):$comp = $matGlobal->comparativaProveedores();$mejorProv = $comp['mas_barato']['proveedor'] ?? ($matGlobal->proveedor->nombre ?? 'Sin proveedor');
                                $mejorPrecio = $comp['mas_barato']['precio'] ?? $matGlobal->precio;
                            ?>
                                <option value="{{ $matGlobal->id }}"
                                        data-codigo="{{ $matGlobal->codigo }}"
                                        data-nombre="{{ $matGlobal->nombre }}"
                                        data-unidad="{{ $matGlobal->unidad_medida }}"
                                        data-proveedor="{{ $mejorProv }}"
                                        data-precio="{{ (float)$mejorPrecio }}">
                                    {{ $matGlobal->codigo }} — {{$matGlobal->nombre }} [{{ $mejorProv }}] (Q{{ number_format((float)$mejorPrecio, 2) }})
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-success w-100" id="btnAgregarMaterialGlobal">
                        <i class="bi bi-plus-lg me-1"></i> Agregar a la lista
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de materiales a cotizar --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-5">
                    <h6 class="fw-bold mb-0 text-primary">Insumos en la solicitud</h6>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="buscadorFiltro" class="form-control border-start-0 ps-0" placeholder="Filtrar materiales agregados...">
                        <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltro">&times;</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaInsumos">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">Código</th>
                        <th style="width: 30%">Material</th>
                        <th style="width: 15%">Proveedor</th>
                        <th style="width: 12%">Precio Reg.</th>
                        <th style="width: 15%">Cantidad a Cotizar</th>
                        <th style="width: 13%" class="text-end">Subtotal Estimado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaInsumos">
                    <?php 
                    $contadorIndex = 0; 
                    if(isset($precios)):
                        foreach ($precios as$item): 
                            $mat =$item->material;
                            $precioUnit = (float)$item->precio;
                    ?>
                        <tr class="fila-material">
                            <td class="col-codigo">
                                <code>{{ $mat->codigo }}</code>
                                <input type="hidden" name="items[{{ $contadorIndex }}][codigo]" value="{{ $mat->codigo }}">
                                <input type="hidden" name="items[{{ $contadorIndex }}][nombre]" value="{{ $mat->nombre }}">
                                <input type="hidden" name="items[{{ $contadorIndex }}][unidad]" value="{{ $mat->unidad_medida }}">
                                <input type="hidden" name="items[{{ $contadorIndex }}][precio]" value="{{ $precioUnit }}">
                                <input type="hidden" name="items[{{ $contadorIndex }}][proveedor]" value="{{ $proveedor->nombre }}">
                            </td>
                            <td class="col-nombre">
                                <div class="fw-bold text-dark">{{ $mat->nombre }}</div>
                                <small class="text-muted">{{ $mat->categoria }}</small>
                            </td>
                            <td class="col-proveedor">
                                <span class="badge bg-primary text-wrap">{{ $proveedor->nombre }}</span>
                            </td>
                            <td>
                                <span class="font-monospace">Q{{ number_format($precioUnit, 2) }}</span>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           name="items[{{ $contadorIndex }}][cantidad]" 
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
                    <?php 
                            $contadorIndex++; 
                        endforeach; 
                    endif;
                    ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Gran Total Estimado:</td>
                        <td class="text-end font-monospace fw-bold text-success fs-5" id="granTotal">
                            Q0.00
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Observaciones y botón PDF --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label for="observaciones" class="form-label fw-bold">Instrucciones o notas adicionales</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Ej. Indicar tiempo de entrega estimado, validez de la oferta, requerimiento de factura..."></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Generar Solicitud de Cotización (PDF)
                </button>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let contadorItems = {{ $contadorIndex }};
    const cuerpoTabla = document.getElementById('cuerpoTablaInsumos');
    const labelGranTotal = document.getElementById('granTotal');
    const buscadorFiltro = document.getElementById('buscadorFiltro');
    const btnLimpiar = document.getElementById('btnLimpiarFiltro');

    const selectGlobal = document.getElementById('selectorMaterialesGlobal');
    let tsGlobal = null;
    if (selectGlobal) {
        tsGlobal = new TomSelect(selectGlobal, {
            create: false,
            maxOptions: 50,
            placeholder: 'Escribe para buscar material en todos los proveedores...',
            allowEmptyOption: true
        });
    }

    function recalcularTotales() {
        let totalGeneral = 0;
        document.querySelectorAll('.input-cantidad').forEach(input => {
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

    document.querySelectorAll('.input-cantidad').forEach(input => {
        input.addEventListener('input', recalcularTotales);
    });

    document.getElementById('btnAgregarMaterialGlobal').addEventListener('click', function () {
        if (!tsGlobal || !tsGlobal.getValue()) {
            alert('Por favor selecciona un material de la lista.');
            return;
        }

        const selectedOption = selectGlobal.querySelector(`option[value="${tsGlobal.getValue()}"]`);
        if (!selectedOption) return;

        const codigo = selectedOption.dataset.codigo;
        const nombre = selectedOption.dataset.nombre;
        const unidad = selectedOption.dataset.unidad;
        const proveedor = selectedOption.dataset.proveedor;
        const precio = parseFloat(selectedOption.dataset.precio) || 0;

        const nuevaFila = document.createElement('tr');
        nuevaFila.className = 'fila-material table-warning';
        nuevaFila.innerHTML = `
            <td class="col-codigo">
                <code>${codigo}</code>
                <input type="hidden" name="items[${contadorItems}][codigo]" value="${codigo}">
                <input type="hidden" name="items[${contadorItems}][nombre]" value="${nombre}">
                <input type="hidden" name="items[${contadorItems}][unidad]" value="${unidad}">
                <input type="hidden" name="items[${contadorItems}][precio]" value="${precio}">
                <input type="hidden" name="items[${contadorItems}][proveedor]" value="${proveedor}">
            </td>
            <td class="col-nombre">
                <div class="fw-bold text-dark">${nombre}</div>
                <small class="text-muted">Insumo adicional</small>
            </td>
            <td class="col-proveedor">
                <span class="badge bg-dark text-wrap">${proveedor}</span>
            </td>
            <td>
                <span class="font-monospace">Q${precio.toFixed(2)}</span>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" 
                           step="any" 
                           min="0" 
                           name="items[${contadorItems}][cantidad]" 
                           class="form-control input-cantidad text-center" 
                           value="1"
                           data-precio="${precio}">
                    <span class="input-group-text">${unidad}</span>
                </div>
            </td>
            <td class="text-end font-monospace fw-bold subtotal-celda">
                Q${precio.toFixed(2)}
            </td>
        `;

        cuerpoTabla.appendChild(nuevaFila);

        nuevaFila.querySelector('.input-cantidad').addEventListener('input', recalcularTotales);
        
        contadorItems++;
        recalcularTotales();
        tsGlobal.clear();
    });

    if (buscadorFiltro) {
        buscadorFiltro.addEventListener('input', function () {
            const texto = this.value.toLowerCase().trim();
            document.querySelectorAll('.fila-material').forEach(fila => {
                const cod = fila.querySelector('.col-codigo').textContent.toLowerCase();
                const nom = fila.querySelector('.col-nombre').textContent.toLowerCase();
                const prov = fila.querySelector('.col-proveedor').textContent.toLowerCase();
                if (cod.includes(texto) || nom.includes(texto) || prov.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });

        btnLimpiar.addEventListener('click', function () {
            buscadorFiltro.value = '';
            document.querySelectorAll('.fila-material').forEach(fila => fila.style.display = '');
            buscadorFiltro.focus();
        });
    }
});
</script>
@endsection