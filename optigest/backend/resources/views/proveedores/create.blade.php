@extends('layouts.app')
@section('titulo', isset($proveedor) ? 'Editar proveedor' : 'Nuevo proveedor')
@section('contenido')
<h2 class="h4 mb-4">{{ isset($proveedor) ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>
<form method="POST" action="{{ isset($proveedor) ? route('proveedores.update', $proveedor) : route('proveedores.store') }}" class="bg-white p-4 rounded shadow-sm" style="max-width:520px;">
    @csrf
    @if(isset($proveedor)) @method('PUT') @endif
    <div class="mb-3"><label class="form-label small">Nombre</label><input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre ?? '') }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label small">NIT</label><input type="text" name="nit" value="{{ old('nit', $proveedor->nit ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Contacto</label><input type="text" name="contacto" value="{{ old('contacto', $proveedor->contacto ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Email</label><input type="email" name="email" value="{{ old('email', $proveedor->email ?? '') }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label small">Dirección</label><input type="text" name="direccion" value="{{ old('direccion', $proveedor->direccion ?? '') }}" class="form-control"></div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
