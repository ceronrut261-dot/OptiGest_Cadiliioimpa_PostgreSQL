<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    /**
     * Valida el token de Google reCAPTCHA v2 contra la API de verificacion
     * de Google. Se usa en login, registro y recuperacion de contrasena
     * para prevenir ataques automatizados (bots, fuerza bruta, spam).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Por favor confirma que no eres un robot.');
            return;
        }

        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            // Si el servidor aun no tiene configuradas las llaves de
            // reCAPTCHA (ej. entorno de desarrollo), no bloqueamos el
            // flujo para no impedir las pruebas locales.
            return;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->successful() || $response->json('success') !== true) {
            $fail('La verificacion captcha fallo. Intenta de nuevo.');
        }
    }
}
