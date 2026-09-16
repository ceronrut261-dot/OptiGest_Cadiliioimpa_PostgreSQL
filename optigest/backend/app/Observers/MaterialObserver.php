<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\HistorialPrecioMaterial;
use Illuminate\Support\Facades\Auth;

class MaterialObserver
{
    public function updating(Material $material): void
    {
        if ($material->isDirty('precio')) {
            HistorialPrecioMaterial::create([
                'material_id'     => $material->id,
                'precio_anterior' => $material->getOriginal('precio'),
                'precio_nuevo'    => $material->precio,
                'proveedor_id'    => $material->proveedor_id,
                'usuario_id'      => Auth::id(),
                'motivo'          => 'Actualizacion de precio',
                'fecha'           => now(),
            ]);
        }
    }

    public function created(Material $material): void
    {
        HistorialPrecioMaterial::create([
            'material_id'     => $material->id,
            'precio_anterior' => $material->precio,
            'precio_nuevo'    => $material->precio,
            'proveedor_id'    => $material->proveedor_id,
            'usuario_id'      => Auth::id(),
            'motivo'          => 'Registro inicial',
            'fecha'           => now(),
        ]);
    }
}
