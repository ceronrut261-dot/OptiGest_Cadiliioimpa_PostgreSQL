<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envia el enlace de recuperacion de contrasena al correo del usuario.
     * Protegido con captcha para evitar abuso (envio masivo de correos).
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'g-recaptcha-response' => ['required', new Recaptcha],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return back()->with('status', __($status));
    }
}
