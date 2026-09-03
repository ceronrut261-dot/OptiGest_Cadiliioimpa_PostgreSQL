@extends('layouts.app')
@section('titulo', isset($cliente) ? 'Editar cliente' : 'Nuevo cliente')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($cliente) ? 'Editar cliente' : 'Nuevo cliente' }}</h2>
<form method="POST" action="{{ isset($cliente) ? route('clientes.update', $cliente) : route('clientes.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    @csrf
    @if(isset($cliente)) @method('PUT') @endif
    @if(!isset($cliente) && request('origen'))
        <input type="hidden" name="origen" value="{{ request('origen') }}">
    @endif
    <div class="mb-3"><label class="form-label small">Nombre</label><input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre ?? '') }}" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label small">Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Dirección</label><input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Email</label><input type="email" name="email" value="{{ old('email', $cliente->email ?? '') }}" class="form-control"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
