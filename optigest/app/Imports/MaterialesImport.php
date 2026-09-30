<?php

namespace App\Imports;

use App\Models\Material;
use App\Models\Proveedor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

/**
 * Importador de inventario (materiales) para OptiGest.
 * Columnas basadas en optigest/database/schema.sql -> tabla `materiales`:
 * codigo, nombre, categoria, descripcion, precio, stock, stock_minimo,
 * unidad_medida, proveedor_id, activo.
 *
 * En el Excel usamos la columna "proveedor" (nombre del proveedor, texto)
 * en vez de "proveedor_id", porque la empresa no conoce los IDs internos;
 * aqui se busca el proveedor por nombre y se resuelve su id automaticamente.
 *
 * Campos opcionales con valor por defecto si la empresa no los maneja:
 * - categoria: si viene vacia, se guarda como "Sin categoría" (se puede
 *   reclasificar despues manualmente desde el sistema).
 * - stock_minimo: si viene vacio, se usa 10 por defecto.
 * - proveedor: si viene vacio, el material se guarda sin proveedor
 *   (proveedor_id = null), ya que no es un campo obligatorio.
 */
class MaterialesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        $proveedorId = null;

        if (!empty($row['proveedor'])) {
            $proveedorId = Proveedor::firstOrCreate(
                ['nombre' => trim($row['proveedor'])]
            )->id;
        }

        return new Material([
            'codigo'        => $row['codigo'],
            'nombre'        => $row['nombre'],
            'categoria'     => $row['categoria'] ?? 'Sin categoría',
            'descripcion'   => $row['descripcion'] ?? null,
            'precio'        => $row['precio'] ?? 0,
            'stock'         => $row['stock'] ?? 0,
            'stock_minimo'  => $row['stock_minimo'] ?? 10,
            'unidad_medida' => $row['unidad_medida'] ?? 'unidad',
            'proveedor_id'  => $proveedorId,
            'activo'        => true,
        ]);
    }

    /**
     * Reglas por fila. Coinciden con las restricciones NOT NULL / UNIQUE
     * de la tabla `materiales` en schema.sql.
     */
    public function rules(): array
    {
        return [
            'codigo'        => 'required|string|max:20|unique:materiales,codigo',
            'nombre'        => 'required|string|max:255',
            'categoria'     => 'nullable|string|max:100',
            'precio'        => 'nullable|numeric|min:0',
            'stock'         => 'nullable|integer|min:0',
            'stock_minimo'  => 'nullable|integer|min:0',
            'unidad_medida' => 'nullable|string|max:30',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'codigo.required' => 'La fila no tiene código de material.',
            'codigo.unique'   => 'Ese código ya existe en el inventario (revisa duplicados en el Excel o en la base de datos).',
            'nombre.required' => 'La fila no tiene nombre de material.',
            'stock.integer'   => 'El stock debe ser un número entero.',
        ];
    }
}
