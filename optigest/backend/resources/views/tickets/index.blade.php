@extends('layouts.app')
@section('titulo', 'Tickets')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Tickets de Servicio</h2>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo ticket</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Código</th><th>Cliente</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th></th></tr></thead>
    <tbody>
    @forelse($tickets as $ticket)
        <tr>
            <td>{{ $ticket->codigo }}</td>
            <td>{{ $ticket->cliente }}</td>
            <td class="text-capitalize">{{ $ticket->prioridad }}</td>
            <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ',$ticket->estado) }}</span></td>
            <td>{{ $ticket->tecnico?->name ?? '—' }}</td>
            <td class="text-end"><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay tickets registrados.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $tickets->links() }}
@endsection
