@extends('layouts.auth')

@section('titulo', 'Crear cuenta')

@section('contenido')
<form method="POST" action="{{ route('register') }}">
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
        <label class="form-label small">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label small">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Registrarme</button>

    <div class="text-center small">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </div>
</form>
@endsection
