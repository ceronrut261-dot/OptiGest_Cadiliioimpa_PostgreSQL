@extends('layouts.app')

@section('titulo', 'Crear usuario')

@section('contenido')
<div class="row">
    <div class="col-md-6">
        <h4 class="mb-3">Crear nuevo usuario</h4>
        <p class="text-muted small">
            Solo un administrador puede crear cuentas para el personal de
            Constru Fontanería Cadiliompa. Elige con cuidado el rol: define
            qué módulos y qué tickets/cotizaciones podrá gestionar.
        </p>

        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small">Nombre completo</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label small">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small">Rol</label>
                <select name="role" class="form-select" required>
                    <option value="">Selecciona un rol...</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol }}" @selected(old('role') === $rol)>{{ ucfirst($rol) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Crear usuario</button>
        </form>
    </div>
</div>
@endsection
