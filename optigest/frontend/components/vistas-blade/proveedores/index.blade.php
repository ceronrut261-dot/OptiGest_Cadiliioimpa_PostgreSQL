@extends('layouts.app')
@section('titulo', 'Proveedores')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Proveedores</h2>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo proveedor</a>
</div>
<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>NIT</th><th>Contacto</th><th>Teléfono</th><th class="text-end">Materiales</th><th></th></tr></thead>
    <tbody>
    @forelse($proveedores as $proveedor)
        <tr>
            <td>{{ $proveedor->nombre }}</td>
            <td>{{ $proveedor->nit }}</td>
            <td>{{ $proveedor->contacto }}</td>
            <td>{{ $proveedor->telefono }}</td>
            <td class="text-end">{{ $proveedor->materiales_count }}</td>
            <td class="text-end">
                <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Desactivar</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay proveedores registrados.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $proveedores->links() }}
@endsection
