@extends('layouts.auth')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
<p class="small text-muted">
    Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label small">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Enviar enlace de recuperación</button>

    <div class="text-center small">
        <a href="{{ route('login') }}">← Volver a iniciar sesión</a>
    </div>
</form>
@endsection
