<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

/**
 * OptiGest es un sistema interno para el personal de Constru Fontanería
 * Cadiliompa: no existe auto-registro público. Solo un administrador
 * autenticado (ver middleware 'role:administrador' en routes/web.php)
 * puede crear cuentas nuevas, y decide explícitamente qué rol recibe
 * cada una (administrador, cotizador o tecnico).
 */
class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register', ['roles' => ['administrador', 'cotizador', 'tecnico']]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:administrador,cotizador,tecnico'],
        ]);

        // El cast 'password' => 'hashed' del modelo User cifra
        // automaticamente con bcrypt al asignar el atributo.
        $user = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
        ]);

        $user->assignRole($datos['role']);

        event(new Registered($user));

        return redirect()->route('usuarios.create')
            ->with('status', "Usuario {$user->name} creado correctamente con el rol '{$datos['role']}'.");
    }
}
