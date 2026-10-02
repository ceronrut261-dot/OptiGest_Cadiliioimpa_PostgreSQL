@extends('layouts.app')
@section('titulo', isset($servicio) ? 'Editar servicio' : 'Nuevo servicio')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($servicio) ? 'Editar servicio ' . $servicio->codigo : 'Nuevo servicio' }}</h2>
<form method="POST" action="{{ isset($servicio) ? route('catalogo.servicios.update', $servicio) : route('catalogo.servicios.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:640px;">
    @csrf
    @if(isset($servicio)) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label small">Nombre del servicio</label>
        <input type="text" name="nombre" class="form-control" required maxlength="255" value="{{ old('nombre', $servicio->nombre ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label small">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label small">Categoría</label>
            <input type="text" name="categoria" class="form-control" maxlength="100" value="{{ old('categoria', $servicio->categoria ?? '') }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label small">Unidad de cobro</label>
            <input type="text" name="unidad" class="form-control" required maxlength="30" value="{{ old('unidad', $servicio->unidad ?? 'servicio') }}">
            <div class="form-text">servicio, hora, punto, metro…</div>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label small">Precio estándar (Q)</label>
            <input type="number" step="0.01" min="0" name="precio_estandar" class="form-control" required value="{{ old('precio_estandar', $servicio->precio_estandar ?? '') }}">
        </div>
    </div>
    @if(isset($servicio))
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" {{ old('activo', $servicio->activo) ? 'checked' : '' }}>
            <label class="form-check-label small" for="activo">Activo (disponible al cotizar)</label>
        </div>
    @endif
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('catalogo.index', ['vista' => 'servicios']) }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
