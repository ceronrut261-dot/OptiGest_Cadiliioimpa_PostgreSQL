<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventarioImportController extends Controller
{
    // Formulario de importación
    public function form()
    {
        return view('inventario.importar');
    }

    // Procesar archivo importado
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $path = $request->file('archivo')->store('importaciones');

        return back()->with('success', 'Archivo importado correctamente: ' . $path);
    }
}
