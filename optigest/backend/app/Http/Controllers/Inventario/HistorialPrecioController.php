<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;

class HistorialPrecioController extends Controller
{
    public function index(Material $material)
    {
        $historial = $material->historialPrecios()
            ->with(['proveedor', 'usuario'])
            ->orderByDesc('fecha')
            ->get();

        return view('inventario.historial_precios', [
            'material'  => $material,
            'historial' => $historial,
        ]);
    }
}
