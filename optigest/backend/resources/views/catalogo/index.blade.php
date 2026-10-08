@extends('layouts.app')

@section('titulo', 'Catálogo')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Catálogo</h2>
</div>

{{-- Pestañas de navegación --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request('tab', 'materiales') === 'materiales' ? 'active fw-bold' : '' }}" 
           href="{{ route('catalogo.index', ['tab' => 'materiales']) }}">
            Materiales (Bodega)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('tab') === 'proveedores' ? 'active fw-bold' : '' }}" 
           href="{{ route('catalogo.index', ['tab' => 'proveedores']) }}">
            <i class="bi bi-shop"></i> Catálogo de Proveedores (Comparador)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('tab') === 'servicios' ? 'active fw-bold' : '' }}" 
           href="{{ route('catalogo.index', ['tab' => 'servicios']) }}">
            Servicios y mano de obra
        </a>
    </li>
</ul>

{{-- ------------------------------------------------------------- --}}
{{-- PESTAÑA 1: MATERIALES EN BODEGA                               --}}
{{-- ------------------------------------------------------------- --}}
@if(request('tab', 'materiales') === 'materiales')
    <form method="GET" action="{{ route('catalogo.index') }}" class="row g-2 mb-4">
        <input type="hidden" name="tab" value="materiales">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre, código o descripción">
        </div>
        <div class="col-md-4">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Buscar</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse($materiales as $mat)
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="small text-muted mb-1">{{ $mat->codigo }} · {{ $mat->categoria }}</div>
                        <h6 class="card-title fw-bold mb-2">{{ $mat->nombre }}</h6>
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <span class="fs-5 fw-bold text-dark">Q{{ number_format($mat->precio, 2) }}</span>
                            <span class="text-muted small">{{ $mat->unidad_medida }}</span>
                        </div>
                        @if($mat->en_bodega)
                            @if($mat->stock <= 0)
                                <span class="badge bg-danger">Agotado</span>
                            @elseif($mat->bajo_stock)
                                <span class="badge bg-warning text-dark">Stock bajo · {{ $mat->stock }}</span>
                            @else
                                <span class="badge bg-success">Disponible · {{ $mat->stock }}</span>
                            @endif
                        @else
                            <span class="badge bg-info text-dark">Solo cotizable</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">No se encontraron materiales.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $materiales->withQueryString()->links() }}</div>

{{-- ------------------------------------------------------------- --}}
{{-- PESTAÑA 2: CATÁLOGO DE PROVEEDORES Y COMPARADOR               --}}
{{-- ------------------------------------------------------------- --}}
@elseif(request('tab') === 'proveedores')
    <div class="alert alert-info py-2 small mb-3">
        <i class="bi bi-info-circle"></i> Escribe el nombre del material para comparar los precios entre proveedores (Durman, Pipsa, etc.) e identificar la opción más cómoda.
    </div>

    <form method="GET" action="{{ route('catalogo.index') }}" class="row g-2 mb-4">
        <input type="hidden" name="tab" value="proveedores">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar material por nombre o código">
        </div>
        <div class="col-md-4">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Buscar en proveedores</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse($materialesComparativa as $mat)
            @php
                $comp = $mat->comparativaProveedores();
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-light text-dark border">{{ $mat->codigo }}</span>
                            <span class="small text-muted">{{ $mat->categoria }}</span>
                        </div>
                        <h6 class="fw-bold mt-2 mb-0">{{ $mat->nombre }}</h6>
                    </div>
                    <div class="card-body">
                        @if($comp && $comp['mas_barato'])
                            <div class="p-2 mb-3 rounded bg-success-subtle border border-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Opción más cómoda
                                    </span>
                                    <span class="fs-5 fw-bold text-success font-monospace">
                                        Q{{ number_format($comp['mas_barato']['precio'], 2) }}
                                    </span>
                                </div>
                                <div class="mt-1 fw-bold text-dark">
                                    {{ $comp['mas_barato']['proveedor'] }}
                                </div>
                            </div>

                            @if(count($comp['otros']) > 0)
                                <h6 class="small text-muted fw-bold mb-2">Otros proveedores que lo venden:</h6>
                                <ul class="list-group list-group-flush small">
                                    @foreach($comp['otros'] as $otro)
                                        @php
                                            $diferencia = $otro['precio'] - $comp['mas_barato']['precio'];
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                                            <span>{{ $otro['proveedor'] }}</span>
                                            <div>
                                                <span class="font-monospace fw-semibold">Q{{ number_format($otro['precio'], 2) }}</span>
                                                <span class="text-danger small ms-1">(+Q{{ number_format($diferencia, 2) }})</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="small text-muted fst-italic">Solo 1 proveedor tiene precio registrado para este material.</div>
                            @endif
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-exclamation-triangle d-block mb-1 fs-4 text-warning"></i>
                                Sin precios de proveedores registrados.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">No se encontraron materiales para comparar.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $materialesComparativa->withQueryString()->links() }}</div>

{{-- ------------------------------------------------------------- --}}
{{-- PESTAÑA 3: SERVICIOS Y MANO DE OBRA                           --}}
{{-- ------------------------------------------------------------- --}}
@elseif(request('tab') === 'servicios')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">Gestión de servicios y tarifas de mano de obra.</p>
        @if(auth()->user()->hasRole('administrador'))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearServicio">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
            </button>
        @endif
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row g-3">
        @forelse($servicios as $srv)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary">{{ $srv->categoria ?? 'General' }}</span>
                            <span class="small text-muted font-monospace">{{ $srv->codigo }}</span>
                        </div>

                        <h6 class="fw-bold mb-1">{{ $srv->nombre }}</h6>

                        @if(!empty($srv->descripcion))
                            <p class="text-muted small mb-3 flex-grow-1">{{ $srv->descripcion }}</p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        <div class="d-flex justify-content-between align-items-baseline pt-2 border-top mt-2">
                            <span class="fs-5 fw-bold text-primary font-monospace">Q{{ number_format($srv->precio_estandar, 2) }}</span>
                            <span class="text-muted small">por {{ $srv->unidad }}</span>
                        </div>

                        @if(auth()->user()->hasRole('administrador'))
                            <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                                <a href="{{ route('catalogo.servicios.edit', $srv) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form action="{{ route('catalogo.servicios.destroy', $srv) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas desactivar este servicio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Desactivar
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-tools d-block mb-2 fs-3 text-secondary"></i>
                No hay servicios registrados en este momento.
            </div>
        @endforelse
    </div>

    @if(auth()->user()->hasRole('administrador'))
        {{-- Modal de creación rápida --}}
        <div class="modal fade" id="modalCrearServicio" tabindex="-1" aria-labelledby="modalCrearServicioLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('catalogo.servicios.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="modalCrearServicioLabel">Nuevo Servicio o Mano de Obra</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del servicio <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Instalación de tubería PVC" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" name="categoria" id="categoria" class="form-control" placeholder="Ej. Plomería, Electricidad, Albañilería">
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="2" placeholder="Detalles u observaciones del trabajo..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_estandar" class="form-label">Precio base (Q) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="precio_estandar" id="precio_estandar" class="form-control" placeholder="0.00" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unidad" class="form-label">Unidad de medida <span class="text-danger">*</span></label>
                                    <input type="text" name="unidad" id="unidad" class="form-control" placeholder="Ej. mt, hr, día, pza" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Servicio</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif

@endsection