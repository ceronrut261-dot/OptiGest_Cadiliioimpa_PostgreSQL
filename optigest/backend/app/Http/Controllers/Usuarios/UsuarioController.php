<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->orderBy('name')->paginate(15);

        return view('usuarios.index', ['usuarios' => $usuarios]);
    }

    /**
     * "Eliminar" un usuario en realidad lo desactiva: ya no puede iniciar
     * sesión (ver AuthenticatedSessionController), pero se conserva todo
     * su historial (cotizaciones creadas, movimientos de inventario,
     * conversaciones con el asistente IA) porque borrarlo de verdad
     * los eliminaría en cascada.
     */
    public function destroy(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('status', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => false]);

        return redirect()->route('usuarios.index')
            ->with('status', "Usuario {$usuario->name} desactivado.");
    }

    public function reactivar(User $usuario)
    {
        $usuario->update(['activo' => true]);

        return redirect()->route('usuarios.index')
            ->with('status', "Usuario {$usuario->name} reactivado.");
    }
}
