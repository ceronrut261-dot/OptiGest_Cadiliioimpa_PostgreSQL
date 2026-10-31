<?php

namespace App\Services;

use App\Models\HistorialPrecioMaterial;
use App\Models\PrecioProveedorMaterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistorialPrecioProveedorService
{
    public static function guardar(
        int $materialId,
        int $proveedorId,
        float $precio
    ): PrecioProveedorMaterial {
        return DB::transaction(function () use (
            $materialId,
            $proveedorId,
            $precio
        ) {
            $registro = PrecioProveedorMaterial::where(
                'material_id',
                $materialId
            )->where(
                'proveedor_id',
                $proveedorId
            )->lockForUpdate()->first();

            $precioNuevo = round($precio, 2);

            if (!$registro) {
                $registro = PrecioProveedorMaterial::create([
                    'material_id' => $materialId,
                    'proveedor_id' => $proveedorId,
                    'precio' => $precioNuevo,
                    'actualizado_en' => now(),
                ]);

                HistorialPrecioMaterial::create([
                    'material_id' => $materialId,
                    'precio_anterior' => $precioNuevo,
                    'precio_nuevo' => $precioNuevo,
                    'proveedor_id' => $proveedorId,
                    'usuario_id' => Auth::id(),
                    'motivo' => 'Precio inicial del proveedor',
                    'fecha' => now(),
                ]);

                return $registro;
            }

            $precioAnterior = round((float) $registro->precio, 2);

            if ($precioAnterior !== $precioNuevo) {
                HistorialPrecioMaterial::create([
                    'material_id' => $materialId,
                    'precio_anterior' => $precioAnterior,
                    'precio_nuevo' => $precioNuevo,
                    'proveedor_id' => $proveedorId,
                    'usuario_id' => Auth::id(),
                    'motivo' => 'Actualización de precio del proveedor',
                    'fecha' => now(),
                ]);
            }

            $registro->update([
                'precio' => $precioNuevo,
                'actualizado_en' => now(),
            ]);

            return $registro;
        });
    }
}
