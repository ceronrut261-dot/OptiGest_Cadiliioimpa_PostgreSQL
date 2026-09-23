@extends('layouts.app')
@section('titulo', isset($proveedor) ? 'Editar proveedor' : 'Nuevo proveedor')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($proveedor) ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>
<form method="POST" action="{{ isset($proveedor) ? route('proveedores.update', $proveedor) : route('proveedores.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    @csrf
    @if(isset($proveedor)) @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label small">Proveedor</label>
        <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre ?? '') }}" class="form-control" required placeholder="Ej. Ferretería El Tornillo">
    </div>
    <div class="mb-3">
        <label class="form-label small">Nombre (contacto)</label>
        <input type="text" name="nombre_contacto" value="{{ old('nombre_contacto', $proveedor->nombre_contacto ?? '') }}" class="form-control" placeholder="Ej. Juan Pérez">
    </div>
    <div class="mb-3">
        <label class="form-label small">Teléfono</label>
        <input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono ?? '') }}" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label small">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $proveedor->descripcion ?? '') }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection