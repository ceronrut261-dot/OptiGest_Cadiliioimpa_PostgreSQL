<?php

namespace App\Http\Controllers;

use App\Imports\MaterialesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controlador dedicado a la importación de inventario desde Excel.
 * Puedes mover este método a tu InventarioController existente
 * si prefieres mantener todo junto.
 */
class InventarioImportController extends Controller
{
    // Muestra el formulario de carga
    public function formulario()
    {
        return view('inventario.importar');
    }

    // Procesa el archivo subido
    public function importar(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB
        ]);

        $import = new MaterialesImport();
        Excel::import($import, $request->file('archivo_excel'));

        $fallos = $import->failures();

        if ($fallos->isNotEmpty()) {
            $mensajes = $fallos->map(function ($fallo) {
                return "Fila {$fallo->row()}: " . implode(', ', $fallo->errors());
            });

            return back()
                ->with('warning', 'Se importaron algunas filas, pero otras fallaron:')
                ->with('errores_importacion', $mensajes);
        }

        return back()->with('success', 'Inventario importado correctamente.');
    }
}
