@extends('layouts.app')
@section('titulo', 'Asistente IA')
@section('contenido')
<h2 class="h4 mb-4"><i class="bi bi-robot"></i> Asistente Inteligente</h2>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('asistente.consultar') }}">
            @csrf
            <label class="form-label small">Pregunta sobre el estado operativo actual</label>
            <div class="input-group">
                <input type="text" name="pregunta" class="form-control" placeholder="Ej. ¿qué materiales están bajo el stock mínimo?" required>
                <button class="btn btn-primary">Preguntar</button>
            </div>
        </form>
    </div>
</div>

@if (session('ultimaRespuesta'))
    <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
        <div class="card-body">
            <p class="small text-muted mb-1">Tu pregunta:</p>
            <p class="fw-semibold">{{ session('ultimaRespuesta')->pregunta }}</p>
            <p class="small text-muted mb-1">Respuesta:</p>
            <p style="white-space:pre-line;">{{ session('ultimaRespuesta')->respuesta }}</p>
        </div>
    </div>
@endif

<h3 class="h6">Historial reciente</h3>
<div class="list-group">
    @forelse($historial as $item)
        <div class="list-group-item">
            <p class="fw-semibold mb-1 small">{{ $item->pregunta }}</p>
            <p class="text-muted small mb-0" style="white-space:pre-line;">{{ $item->respuesta }}</p>
        </div>
    @empty
        <div class="list-group-item text-muted">Aún no has hecho preguntas al asistente.</div>
    @endforelse
</div>
@endsection
