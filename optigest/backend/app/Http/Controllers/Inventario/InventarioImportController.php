<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Importador de inventario SIN dependencias externas (sin Composer).
 * Lee un archivo CSV con PHP puro (fgetcsv) y lo carga a `materiales`.
 *
 * Encabezados esperados en el CSV (primera fila):
 * codigo,nombre,categoria,descripcion,precio,stock,stock_minimo,unidad_medida,proveedor
 * Solo codigo y nombre son obligatorios; el resto tiene valores por
 * defecto si vienen vacíos.
 */
class InventarioImportController extends Controller
{
    // Formulario de importación (sin cambios, ya existía)
    public function form()
    {
        return view('inventario.importar');
    }

    // Procesar archivo CSV importado
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('archivo')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('warning', 'No se pudo abrir el archivo.');
        }

        // Lee la primera fila como encabezados
        $encabezados = fgetcsv($handle);
        $encabezados = array_map(fn ($h) => strtolower(trim($h)), $encabezados);

        $fila = 1;
        $errores = [];
        $importados = 0;

        while (($datos = fgetcsv($handle)) !== false) {
            $fila++;

            // Salta filas completamente vacías
            if (count(array_filter($datos, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = array_combine($encabezados, $datos);

            $validator = Validator::make($row, [
                'codigo' => 'required|string|max:20|unique:materiales,codigo',
                'nombre' => 'required|string|max:255',
                'precio' => 'nullable|numeric|min:0',
                'stock'  => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                $errores[] = "Fila {$fila}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            $proveedorId = null;
            if (!empty($row['proveedor'])) {
                $proveedorId = Proveedor::firstOrCreate(
                    ['nombre' => trim($row['proveedor'])]
                )->id;
            }

            Material::create([
                'codigo'        => trim($row['codigo']),
                'nombre'        => trim($row['nombre']),
                'categoria'     => $row['categoria'] !== '' ? $row['categoria'] : 'Sin categoría',
                'descripcion'   => $row['descripcion'] ?? null,
                'precio'        => $row['precio'] !== '' ? $row['precio'] : 0,
                'stock'         => $row['stock'] !== '' ? $row['stock'] : 0,
                'stock_minimo'  => $row['stock_minimo'] !== '' ? $row['stock_minimo'] : 10,
                'unidad_medida' => $row['unidad_medida'] !== '' ? $row['unidad_medida'] : 'unidad',
                'proveedor_id'  => $proveedorId,
                'activo'        => true,
            ]);

            $importados++;
        }

        fclose($handle);

        if (!empty($errores)) {
            return back()
                ->with('warning', "Se importaron {$importados} materiales. Algunas filas fallaron:")
                ->with('errores_importacion', $errores);
        }

        return back()->with('success', "Se importaron {$importados} materiales correctamente.");
    }
}
