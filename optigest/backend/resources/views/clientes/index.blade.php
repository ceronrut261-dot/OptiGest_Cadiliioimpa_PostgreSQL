@extends('layouts.app')
@section('titulo', 'Clientes')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" style="max-width:320px;">
        @if($verInactivos)
            <input type="hidden" name="inactivos" value="1">
        @endif
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Buscar por nombre o teléfono...">
    </form>

    <a href="{{ route('clientes.index', $verInactivos ? [] : ['inactivos' => 1]) }}" class="btn btn-sm btn-outline-secondary">
        {{ $verInactivos ? 'Ver solo activos' : 'Ver desactivados' }}
    </a>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th class="text-end">Tickets</th><th class="text-end">Cotizaciones</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse($clientes as $cliente)
        <tr class="{{ $cliente->activo ? '' : 'table-secondary text-muted' }}">
            <td>{{ $cliente->nombre }}</td>
            <td>{{ $cliente->telefono }}</td>
            <td>{{ $cliente->direccion }}</td>
            <td class="text-end">{{ $cliente->tickets_count }}</td>
            <td class="text-end">{{ $cliente->cotizaciones_count }}</td>
            <td>
                @if($cliente->activo)
                    <span class="badge bg-success">Activo</span>
                @else
                    <span class="badge bg-secondary">Desactivado</span>
                @endif
            </td>
            <td class="text-end">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                @if($cliente->activo)
                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Desactivar a {{ $cliente->nombre }}? No se borra su historial, solo deja de aparecer en la lista activa.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                @else
                    <form action="{{ route('clientes.reactivar', $cliente) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No hay clientes {{ $verInactivos ? 'desactivados' : 'registrados' }}.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $clientes->links() }}
@endsection