@extends('layouts.auth')

@section('titulo', 'Restablecer contraseña')

@section('contenido')
<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label small">Nueva contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Confirmar nueva contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
</form>
@endsection
