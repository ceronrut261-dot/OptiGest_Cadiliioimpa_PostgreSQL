<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaLog;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $consulta = AuditoriaLog::with('usuario')
            ->orderByDesc('created_at');

        if ($request->filled('accion')) {
            $consulta->where('accion', $request->accion);
        }

        if ($request->filled('modulo')) {
            $consulta->where('modulo', $request->modulo);
        }

        if ($request->filled('desde')) {
            $consulta->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $consulta->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $consulta->where(function ($query) use ($buscar) {
                $query->where('descripcion', 'ilike', "%{$buscar}%")
                    ->orWhere('registro_tipo', 'ilike', "%{$buscar}%")
                    ->orWhere('ip_address', 'ilike', "%{$buscar}%");
            });
        }

        $registros = $consulta->paginate(20)->withQueryString();

        return view('auditoria.index', compact('registros'));
    }
}
