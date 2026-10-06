@extends('layouts.app')
@section('titulo', 'Usuarios')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Usuarios</h2>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Crear usuario</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
    <thead class="table-light"><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse($usuarios as $usuario)
        <tr class="{{ $usuario->activo ? '' : 'table-secondary text-muted' }}">
            <td>{{ $usuario->name }}</td>
            <td>{{ $usuario->email }}</td>
            <td>{{ $usuario->roles->pluck('name')->implode(', ') ?: '—' }}</td>
            <td>
                @if($usuario->activo)
                    <span class="badge bg-success">Activo</span>
                @else
                    <span class="badge bg-secondary">Desactivado</span>
                @endif
            </td>
            <td class="text-end">
                @if($usuario->id === auth()->id())
                    <span class="text-muted small">(tu cuenta)</span>
                @elseif($usuario->activo)
                    <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Desactivar a {{ $usuario->name }}? No podrá iniciar sesión, pero su historial se conserva.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                @else
                    <form action="{{ route('usuarios.reactivar', $usuario) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $usuarios->links() }}
@endsection