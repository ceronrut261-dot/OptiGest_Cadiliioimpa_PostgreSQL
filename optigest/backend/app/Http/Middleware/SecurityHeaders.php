<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad HTTP para todas las respuestas web.
 *
 * Registro en bootstrap/app.php (Laravel 11):
 *
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->web(append: [\App\Http\Middleware\SecurityHeaders::class]);
 *   })
 *
 * No se activa Content-Security-Policy estricta a proposito: el sistema carga
 * Bootstrap, TomSelect y reCAPTCHA desde CDN y habria que listarlos uno a uno.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');          // anti clickjacking
        $response->headers->set('X-Content-Type-Options', 'nosniff');      // evita adivinar tipos MIME
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
