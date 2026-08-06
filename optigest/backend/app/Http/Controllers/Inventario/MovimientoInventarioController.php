<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['material', 'usuario', 'cotizacion'])->orderByDesc('fecha');

        if ($materialId = $request->get('material_id')) {
            $query->where('material_id', $materialId);
        }

        if ($tipo = $request->get('tipo')) {
            $query->where('tipo', $tipo);
        }

        return view('inventario.movimientos.index', [
            'movimientos' => $query->paginate(20)->withQueryString(),
            'materiales' => Material::orderBy('nombre')->get(['id', 'nombre', 'codigo']),
        ]);
    }

    public function create()
    {
        return view('inventario.movimientos.create', [
            'materiales' => Material::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'material_id' => ['required', 'exists:materiales,id'],
            'tipo' => ['required', 'in:entrada,salida'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($datos, $request) {
            $material = Material::lockForUpdate()->findOrFail($datos['material_id']);

            if ($datos['tipo'] === MovimientoInventario::TIPO_SALIDA && $datos['cantidad'] > $material->stock) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente. Stock actual de {$material->nombre}: {$material->stock}.",
                ]);
            }

            $material->stock = $datos['tipo'] === MovimientoInventario::TIPO_ENTRADA
                ? $material->stock + $datos['cantidad']
                : $material->stock - $datos['cantidad'];
            $material->save();

            MovimientoInventario::create([
                'material_id' => $material->id,
                'tipo' => $datos['tipo'],
                'cantidad' => $datos['cantidad'],
                'motivo' => $datos['motivo'] ?? 'Movimiento manual de bodega',
                'fecha' => now(),
                'usuario_id' => $request->user()->id,
                'stock_resultante' => $material->stock,
            ]);
        });

        return redirect()->route('inventario.movimientos.index')
            ->with('status', 'Movimiento de inventario registrado correctamente.');
    }

    public static function registrarSalidaPorCotizacion(Material $material, int $cantidad, int $usuarioId, int $cotizacionId): void
    {
        $material->refresh();
        $material->stock = max(0, $material->stock - $cantidad);
        $material->save();

        MovimientoInventario::create([
            'material_id' => $material->id,
            'tipo' => MovimientoInventario::TIPO_SALIDA,
            'cantidad' => $cantidad,
            'motivo' => 'Descuento automatico por aprobacion de cotizacion',
            'fecha' => now(),
            'usuario_id' => $usuarioId,
            'cotizacion_id' => $cotizacionId,
            'stock_resultante' => $material->stock,
        ]);
    }
}
