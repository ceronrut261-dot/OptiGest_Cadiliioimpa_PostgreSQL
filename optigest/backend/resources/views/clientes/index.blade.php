@extends('layouts.app')
@section('titulo', 'Clientes')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
</div>

<form method="GET" class="mb-3" style="max-width:320px;">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Buscar por nombre o teléfono...">
</form>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th class="text-end">Tickets</th><th class="text-end">Cotizaciones</th><th></th></tr></thead>
    <tbody>
    @forelse($clientes as $cliente)
        <tr>
            <td>{{ $cliente->nombre }}</td>
            <td>{{ $cliente->telefono }}</td>
            <td>{{ $cliente->direccion }}</td>
            <td class="text-end">{{ $cliente->tickets_count }}</td>
            <td class="text-end">{{ $cliente->cotizaciones_count }}</td>
            <td class="text-end">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-primary">Editar</a>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay clientes registrados.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $clientes->links() }}
@endsection
