@extends('layouts.app')
@section('titulo', isset($ticket) ? 'Editar ticket' : 'Nuevo ticket')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($ticket) ? 'Editar ticket' : 'Nuevo ticket' }}</h2>
<form method="POST" action="{{ isset($ticket) ? route('tickets.update', $ticket) : route('tickets.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    @csrf
    @if(isset($ticket)) @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label small">Cliente</label>
        <div class="d-flex gap-2">
            <select name="cliente_id" class="form-select" required>
                <option value="">— Selecciona un cliente —</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ old('cliente_id', $ticket->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nombre }}{{ $cliente->telefono ? ' — '.$cliente->telefono : '' }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('clientes.create', ['origen' => 'ticket']) }}" target="_blank" class="btn btn-outline-secondary text-nowrap">Cliente nuevo</a>
        </div>
        <div class="form-text">¿No aparece? Créalo en la pestaña nueva y refresca esta lista.</div>
    </div>
    <div class="mb-3"><label class="form-label small">Descripción</label><textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $ticket->descripcion ?? '') }}</textarea></div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Prioridad</label>
            <select name="prioridad" class="form-select">
                @foreach(['baja','media','alta','urgente'] as $p)
                    <option value="{{ $p }}" {{ old('prioridad', $ticket->prioridad ?? 'media') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label small">Técnico asignado</label>
            <select name="tecnico_id" class="form-select">
                <option value="">— Sin asignar —</option>
                @foreach($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $ticket->tecnico_id ?? '') == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3"><label class="form-label small">Fecha programada</label><input type="datetime-local" name="fecha_programada" class="form-control" value="{{ old('fecha_programada', isset($ticket->fecha_programada) ? $ticket->fecha_programada->format('Y-m-d\TH:i') : '') }}"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
