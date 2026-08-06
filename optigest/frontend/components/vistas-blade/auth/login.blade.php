@extends('layouts.auth')

@section('titulo', 'Iniciar sesión')

@section('contenido')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label small">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" class="form-check-input" id="remember">
        <label class="form-check-label small" for="remember">Recordarme</label>
    </div>

    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Iniciar sesión</button>

    <div class="d-flex justify-content-between small">
        <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        <a href="{{ route('register') }}">Crear cuenta</a>
    </div>
</form>
@endsection
