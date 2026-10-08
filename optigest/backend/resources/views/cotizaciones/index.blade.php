@extends('layouts.app')
@section('titulo', 'Cotizaciones')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotizaciones</h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCompararProveedores">
            <i class="bi bi-tags me-1"></i> Cotizar entre proveedores
        </button>
        <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Nueva cotización
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle shadow-sm">
        <thead class="table-light">
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-end">Total</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($cotizaciones->isEmpty()): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No hay cotizaciones registradas.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($cotizaciones as$cot): ?>
                <tr>
                    <td><code>{{ $cot->codigo }}</code></td>
                    <td>{{ $cot->cliente->nombre ?? 'Sin cliente' }}</td>
                    <td><span class="badge bg-secondary text-capitalize">{{ $cot->estado }}</span></td>
                    <td class="text-end font-monospace">Q{{ number_format($cot->total, 2) }}</td>
                    <td>{{ $cot->fecha ? $cot->fecha->format('d/m/Y') : ($cot->created_at ? $cot->created_at->format('d/m/Y') : '-') }}</td>
                    <td class="text-end">
                        <a href="{{ route('cotizaciones.show', $cot) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

{{ $cotizaciones->links() }}

<!-- Modal Comparador de Proveedores -->
<div class="modal fade" id="modalCompararProveedores" tabindex="-1" aria-labelledby="modalCompararLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title h6 fw-bold mb-0 text-primary" id="modalCompararLabel">
                        <i class="bi bi-tags me-1"></i> Comparar precios entre proveedores
                    </h5>
                    <small class="text-muted">Busca un material por código o nombre para ver qué proveedor lo tiene más barato</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Buscador de material -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="buscadorMaterialComparador" class="form-control" placeholder="Escribe el código o nombre del material (ej. PVC, Codo, Tubo, Durman)..." autofocus>
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarComparador">&times;</button>
                </div>

                <!-- Estado inicial / cargando -->
                <div id="comparadorEstado" class="text-center py-4 text-muted small">
                    <i class="bi bi-arrow-up-circle fs-4 d-block mb-1 text-primary"></i>
                    Escribe al menos 2 letras o números para buscar y comparar precios.
                </div>

                <!-- Contenedor de la tabla de resultados -->
                <div id="contenedorResultadosComparador" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Proveedor</th>
                                    <th class="text-end">Precio Compra</th>
                                    <th class="text-center">Comparativa</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaComparador">
                                <!-- Se renderiza por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar = document.getElementById('buscadorMaterialComparador');
    const estado = document.getElementById('comparadorEstado');
    const contenedor = document.getElementById('contenedorResultadosComparador');
    const tabla = document.getElementById('cuerpoTablaComparador');
    const btnLimpiar = document.getElementById('btnLimpiarComparador');

    let timeoutBusqueda = null;

    inputBuscar.addEventListener('input', function () {
        clearTimeout(timeoutBusqueda);
        const query = this.value.trim();

        if (query.length < 2) {
            estado.style.display = 'block';
            estado.innerHTML = '<i class="bi bi-arrow-up-circle fs-4 d-block mb-1 text-primary"></i> Escribe al menos 2 letras o números para buscar.';
            contenedor.style.display = 'none';
            return;
        }

        estado.style.display = 'block';
        estado.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Buscando y comparando proveedores...';
        contenedor.style.display = 'none';

        timeoutBusqueda = setTimeout(() => {
            fetch(`/api/materiales/comparar-precios?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    renderizarResultados(data);
                })
                .catch(() => {
                    estado.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> No se pudo consultar la comparación en este momento.</span>';
                });
        }, 300);
    });

    btnLimpiar.addEventListener('click', function () {
        inputBuscar.value = '';
        estado.style.display = 'block';
        estado.innerHTML = '<i class="bi bi-arrow-up-circle fs-4 d-block mb-1 text-primary"></i> Escribe al menos 2 letras o números para buscar.';
        contenedor.style.display = 'none';
        inputBuscar.focus();
    });

    function renderizarResultados(materiales) {
        if (!materiales || materiales.length === 0) {
            estado.innerHTML = '<i class="bi bi-search text-muted d-block fs-4 mb-1"></i> No se encontraron materiales que coincidan con la búsqueda.';
            return;
        }

        estado.style.display = 'none';
        contenedor.style.display = 'block';
        tabla.innerHTML = '';

        materiales.forEach(mat => {
            if (!mat.precios || mat.precios.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <code>${mat.codigo}</code>
                        <div class="fw-bold">${mat.nombre}</div>
                    </td>
                    <td colspan="4" class="text-muted fst-italic">Sin precios de proveedores vinculados</td>
                `;
                tabla.appendChild(tr);
                return;
            }

            mat.precios.sort((a, b) => parseFloat(a.precio) - parseFloat(b.precio));
            const menorPrecio = parseFloat(mat.precios[0].precio);

            mat.precios.forEach(p => {
                const precioActual = parseFloat(p.precio);
                const esMasBarato = (precioActual === menorPrecio);
                const tr = document.createElement('tr');

                if (esMasBarato && mat.precios.length > 1) {
                    tr.classList.add('table-success');
                }

                tr.innerHTML = `
                    <td>
                        <code>${mat.codigo}</code>
                        <div class="fw-semibold text-dark">${mat.nombre}</div>
                    </td>
                    <td>
                        <span class="fw-bold">${p.proveedor.nombre}</span>
                    </td>
                    <td class="text-end font-monospace fw-bold text-nowrap">
                        Q${precioActual.toFixed(2)}
                    </td>
                    <td class="text-center">
                        ${esMasBarato 
                            ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Más barato</span>' 
                            : '<span class="badge bg-light text-muted border">Más caro</span>'}
                    </td>
                    <td class="text-end">
                        <a href="/proveedores/${p.proveedor_id}/solicitud" class="btn btn-outline-primary btn-sm py-0 px-2" title="Cotizar a este proveedor">
                            Cotizar
                        </a>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        });
    }
});
</script>
@endsection