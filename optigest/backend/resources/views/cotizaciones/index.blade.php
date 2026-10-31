
@extends('layouts.app')

@section('titulo', 'Cotizaciones')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Cotizaciones</h2>

    <div class="d-flex gap-2">
        <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modalCompararProveedores"
        >
            <i class="bi bi-tags me-1"></i>
            Cotizar entre proveedores
        </button>

        <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>
            Nueva cotización
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>No se pudo completar la operación.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-sm table-hover bg-white align-middle shadow-sm">
        <thead class="table-light">
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-end">Total</th>
                <th>Fecha</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse($cotizaciones as $cot)
                <tr>
                    <td>
                        <code>{{ $cot->codigo }}</code>
                    </td>

                    <td>
                        {{ $cot->cliente->nombre ?? 'Sin cliente' }}
                    </td>

                    <td>
                        @php
                            $claseEstado = match($cot->estado) {
                                'borrador' => 'bg-secondary',
                                'enviada' => 'bg-primary',
                                'aprobada' => 'bg-success',
                                'rechazada' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp

                        <span class="badge {{ $claseEstado }} text-capitalize">
                            {{ $cot->estado }}
                        </span>
                    </td>

                    <td class="text-end font-monospace">
                        Q{{ number_format($cot->total, 2) }}
                    </td>

                    <td>
                        {{ $cot->fecha
                            ? $cot->fecha->format('d/m/Y')
                            : ($cot->created_at
                                ? $cot->created_at->format('d/m/Y')
                                : '-') }}
                    </td>

                    <td class="text-end text-nowrap">
                        <a
                            href="{{ route('cotizaciones.show', $cot) }}"
                            class="btn btn-sm btn-outline-primary py-1 px-2"
                            title="Ver cotización"
                        >
                            <i class="bi bi-eye"></i>
                            Ver
                        </a>

                        @if($cot->estado === 'borrador')
                            <a
                                href="{{ route('cotizaciones.edit', $cot) }}"
                                class="btn btn-sm btn-outline-warning py-1 px-2"
                                title="Editar cotización"
                            >
                                <i class="bi bi-pencil-square"></i>
                                Editar
                            </a>

                            <form
                                action="{{ route('cotizaciones.destroy', $cot) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('¿Estás segura de que deseas eliminar la cotización {{ $cot->codigo }}? Esta acción no se puede deshacer.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-danger py-1 px-2"
                                    title="Eliminar cotización"
                                >
                                    <i class="bi bi-trash"></i>
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No hay cotizaciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $cotizaciones->links() }}
</div>

<!-- Modal Comparador de Proveedores -->
<div
    class="modal fade"
    id="modalCompararProveedores"
    tabindex="-1"
    aria-labelledby="modalCompararLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title h6 fw-bold mb-0 text-primary" id="modalCompararLabel">
                        <i class="bi bi-tags me-1"></i>
                        Comparar precios entre proveedores
                    </h5>

                    <small class="text-muted">
                        Busca un material por código o nombre para ver qué proveedor lo tiene más barato.
                    </small>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>
            </div>

            <div class="modal-body p-4">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>

                    <input
                        type="text"
                        id="buscadorMaterialComparador"
                        class="form-control"
                        placeholder="Escribe el código o nombre del material (ej. PVC, Codo, Tubo, Durman)..."
                        autocomplete="off"
                    >

                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        id="btnLimpiarComparador"
                        title="Limpiar búsqueda"
                    >
                        &times;
                    </button>
                </div>

                <div id="comparadorEstado" class="text-center py-4 text-muted small">
                    <i class="bi bi-arrow-up-circle fs-4 d-block mb-1 text-primary"></i>
                    Escribe al menos 2 letras o números para buscar y comparar precios.
                </div>

                <div id="contenedorResultadosComparador" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Proveedor</th>
                                    <th class="text-end">Precio compra</th>
                                    <th class="text-center">Comparativa</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>

                            <tbody id="cuerpoTablaComparador"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cerrar
                </button>
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
    let controladorBusqueda = null;

    inputBuscar.addEventListener('input', function () {
        clearTimeout(timeoutBusqueda);

        if (controladorBusqueda) {
            controladorBusqueda.abort();
        }

        const query = this.value.trim();

        if (query.length < 2) {
            estado.style.display = 'block';
            estado.textContent = 'Escribe al menos 2 letras o números para buscar.';
            contenedor.style.display = 'none';
            tabla.replaceChildren();
            return;
        }

        estado.style.display = 'block';
        estado.textContent = 'Buscando y comparando proveedores...';
        contenedor.style.display = 'none';

        timeoutBusqueda = setTimeout(() => {
            controladorBusqueda = new AbortController();

            fetch(`/api/materiales/comparar-precios?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json'
                },
                signal: controladorBusqueda.signal
            })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Error al consultar los precios.');
                    }

                    return res.json();
                })
                .then(data => {
                    renderizarResultados(data);
                })
                .catch(error => {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    estado.style.display = 'block';
                    estado.textContent = 'No se pudo consultar la comparación en este momento.';
                    contenedor.style.display = 'none';
                });
        }, 300);
    });

    btnLimpiar.addEventListener('click', function () {
        clearTimeout(timeoutBusqueda);

        if (controladorBusqueda) {
            controladorBusqueda.abort();
        }

        inputBuscar.value = '';
        estado.style.display = 'block';
        estado.textContent = 'Escribe al menos 2 letras o números para buscar.';
        contenedor.style.display = 'none';
        tabla.replaceChildren();
        inputBuscar.focus();
    });

    function agregarCelda(fila, contenido, clase = '') {
        const celda = document.createElement('td');

        if (clase) {
            celda.className = clase;
        }

        celda.textContent = contenido;
        fila.appendChild(celda);

        return celda;
    }

    function renderizarResultados(materiales) {
        tabla.replaceChildren();

        if (!Array.isArray(materiales) || materiales.length === 0) {
            estado.style.display = 'block';
            estado.textContent = 'No se encontraron materiales que coincidan con la búsqueda.';
            contenedor.style.display = 'none';
            return;
        }

        estado.style.display = 'none';
        contenedor.style.display = 'block';

        materiales.forEach(mat => {
            const precios = Array.isArray(mat.precios)
                ? [...mat.precios].sort(
                    (a, b) => parseFloat(a.precio) - parseFloat(b.precio)
                )
                : [];

            if (precios.length === 0) {
                const fila = document.createElement('tr');
                const celdaMaterial = fila.insertCell();

                const codigo = document.createElement('code');
                codigo.textContent = mat.codigo ?? '';

                const nombre = document.createElement('div');
                nombre.className = 'fw-bold';
                nombre.textContent = mat.nombre ?? '';

                celdaMaterial.append(codigo, nombre);

                const celdaMensaje = fila.insertCell();
                celdaMensaje.colSpan = 4;
                celdaMensaje.className = 'text-muted fst-italic';
                celdaMensaje.textContent = 'Sin precios de proveedores vinculados.';

                tabla.appendChild(fila);
                return;
            }

            const menorPrecio = parseFloat(precios[0].precio);

            precios.forEach(p => {
                const fila = document.createElement('tr');
                const precioActual = parseFloat(p.precio);
                const esMasBarato = precioActual === menorPrecio;
                const proveedor = p.proveedor ?? {};

                if (esMasBarato && precios.length > 1) {
                    fila.classList.add('table-success');
                }

                const celdaMaterial = fila.insertCell();

                const codigo = document.createElement('code');
                codigo.textContent = mat.codigo ?? '';

                const nombre = document.createElement('div');
                nombre.className = 'fw-semibold text-dark';
                nombre.textContent = mat.nombre ?? '';

                celdaMaterial.append(codigo, nombre);

                agregarCelda(
                    fila,
                    proveedor.nombre ?? 'Sin proveedor'
                );

                agregarCelda(
                    fila,
                    `Q${Number.isFinite(precioActual) ? precioActual.toFixed(2) : '0.00'}`,
                    'text-end font-monospace fw-bold text-nowrap'
                );

                const celdaComparativa = fila.insertCell();
                celdaComparativa.className = 'text-center';

                const etiqueta = document.createElement('span');

                if (esMasBarato) {
                    etiqueta.className = 'badge bg-success';
                    etiqueta.textContent = 'Más barato';
                } else {
                    etiqueta.className = 'badge bg-light text-muted border';
                    etiqueta.textContent = 'Más caro';
                }

                celdaComparativa.appendChild(etiqueta);

                const celdaAccion = fila.insertCell();
                celdaAccion.className = 'text-end';

                const proveedorId = Number(p.proveedor_id);

                if (Number.isInteger(proveedorId) && proveedorId > 0) {
                    const enlace = document.createElement('a');
                    enlace.href = `/proveedores/${proveedorId}/solicitud-cotizacion`;
                    enlace.target = '_blank';
                    enlace.rel = 'noopener noreferrer';
                    enlace.className = 'btn btn-outline-primary btn-sm py-0 px-2';
                    enlace.title = 'Abrir solicitud de cotización para este proveedor';
                    enlace.textContent = 'Cotizar';

                    celdaAccion.appendChild(enlace);
                } else {
                    celdaAccion.textContent = 'No disponible';
                }

                tabla.appendChild(fila);
            });
        });
    }
});
</script>
@endsection
