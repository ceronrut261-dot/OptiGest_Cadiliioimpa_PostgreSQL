@extends('layouts.app')
@section('titulo', 'Ticket ' . $ticket->codigo)
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Ticket {{ $ticket->codigo }}</h2>
    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-primary btn-sm">Editar</a>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Cliente:</strong> {{ $ticket->cliente }}</p>
        <p><strong>Descripción:</strong> {{ $ticket->descripcion }}</p>
        <p><strong>Técnico:</strong> {{ $ticket->tecnico?->name ?? '—' }}</p>
        <p><strong>Estado actual:</strong> <span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ',$ticket->estado) }}</span></p>

        <form action="{{ route('tickets.estado', $ticket) }}" method="POST" class="d-flex gap-2 mt-3">
            @csrf @method('PATCH')
            <select name="estado" class="form-select form-select-sm" style="max-width:220px;">
                @foreach(['pendiente','asignado','en_proceso','completado','cancelado'] as $estado)
                    <option value="{{ $estado }}" {{ $ticket->estado == $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Actualizar estado</button>
        </form>
    </div>
</div>

<h3 class="h6">Cotizaciones relacionadas</h3>
<ul class="list-group">
    @forelse($ticket->cotizaciones as $cot)
        <li class="list-group-item d-flex justify-content-between">
            <a href="{{ route('cotizaciones.show', $cot) }}">{{ $cot->codigo }}</a>
            <span class="badge bg-secondary text-capitalize">{{ $cot->estado }}</span>
        </li>
    @empty
        <li class="list-group-item text-muted">Sin cotizaciones asociadas.</li>
    @endforelse
</ul>
@endsection
