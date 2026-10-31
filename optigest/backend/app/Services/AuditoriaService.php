<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    public function registrar(
        string $accion,
        string $modulo,
        ?Model $registro = null,
        ?array $anteriores = null,
        ?array $nuevos = null,
        ?string $descripcion = null
    ): void {
        $request = app()->bound('request') ? request() : null;

        AuditoriaLog::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'modulo' => $modulo,
            'registro_tipo' => $registro ? class_basename($registro) : null,
            'registro_id' => $registro?->getKey(),
            'descripcion' => $descripcion,
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $nuevos,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request
                ? substr((string) $request->userAgent(), 0, 2000)
                : null,
            'created_at' => now(),
        ]);
    }
}
