@extends('layouts.app')
@section('titulo', 'Catálogo')
@section('contenido')
@php $verPrecios = auth()->user()->hasRole(['administrador', 'cotizador']); @endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Catálogo</h2>
    @if($puedeGestionar && $vista === 'servicios')
        <a href="{{ route('catalogo.servicios.create') }}" class="btn btn-primary btn-sm">+ Nuevo servicio</a>
    @endif
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ $vista === 'materiales' ? 'active' : '' }}" href="{{ route('catalogo.index', ['vista' => 'materiales']) }}">Materiales</a></li>
    <li class="nav-item"><a class="nav-link {{ $vista === 'servicios' ? 'active' : '' }}" href="{{ route('catalogo.index', ['vista' => 'servicios']) }}">Servicios y mano de obra</a></li>
</ul>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="vista" value="{{ $vista }}">
    <div class="col-md-5"><input type="search" name="q" value="{{ $buscar }}" class="form-control form-control-sm" placeholder="Buscar por nombre, código o descripción"></div>
    @if($vista === 'materiales')
        <div class="col-md-4">
            <select name="categoria" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat }}" {{ $categoria === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Buscar</button></div>
</form>

@if($vista === 'materiales')
    <div class="row g-3">
        @forelse($materiales as $m)
            @php
                $agotado = $m->stock <= 0;
                $bajo = ! $agotado && $m->bajo_stock;
            @endphp
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="small text-muted">{{ $m->codigo }}@if($m->categoria) · {{ $m->categoria }}@endif</div>
                        <h3 class="h6 mt-1">{{ $m->nombre }}</h3>
                        @if($m->descripcion)<p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($m->descripcion, 70) }}</p>@endif
                        <div class="d-flex justify-content-between align-items-end">
                            @if($verPrecios)<span class="fw-semibold">Q{{ number_format($m->precio, 2) }}</span>@else<span></span>@endif
                            <span class="small text-muted">{{ $m->unidad_medida }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        @if($agotado)
                            <span class="badge bg-danger">Agotado</span>
                        @elseif($bajo)
                            <span class="badge bg-warning text-dark">Stock bajo · {{ $m->stock }}</span>
                        @else
                            <span class="badge bg-success">Disponible · {{ $m->stock }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">No se encontraron materiales.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $materiales->links() }}</div>
@else
    <div class="table-responsive">
        <table class="table table-sm bg-white align-middle">
            <thead class="table-light">
                <tr><th>Código</th><th>Servicio</th><th>Categoría</th><th>Unidad</th><th class="text-end">Precio estándar</th>@if($puedeGestionar)<th></th>@endif</tr>
            </thead>
            <tbody>
            @forelse($servicios as $s)
                <tr class="{{ $s->activo ? '' : 'text-muted' }}">
                    <td>{{ $s->codigo }}</td>
                    <td>{{ $s->nombre }}@unless($s->activo) <span class="badge bg-secondary">Inactivo</span>@endunless
                        @if($s->descripcion)<div class="small text-muted">{{ $s->descripcion }}</div>@endif</td>
                    <td>{{ $s->categoria ?? '—' }}</td>
                    <td>{{ $s->unidad }}</td>
                    <td class="text-end">Q{{ number_format($s->precio_estandar, 2) }}</td>
                    @if($puedeGestionar)
                        <td class="text-end text-nowrap">
                            <a href="{{ route('catalogo.servicios.edit', $s) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                            @if($s->activo)
                                <form action="{{ route('catalogo.servicios.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar este servicio?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Desactivar</button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted text-center py-3">Todavía no hay servicios en el catálogo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="small text-muted">Estos precios de mano de obra se usan tal cual en las cotizaciones, para que no varíen de un trabajo a otro.</p>
@endif
@endsection
