<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MovimientoInventario;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'materiales' => Material::orderByRaw("CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC")->get(['id', 'nombre', 'codigo']),
        ]);
    }

    public function create()
    {
        return view('inventario.movimientos.create', [
            'materiales' => Material::where('activo', true)->orderByRaw("CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC")->get(),
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

            if ($datos['tipo'] === MovimientoInventario::TIPO_SALIDA) {
                // Bloqueo pedido por la empresa: si el material ya está
                // en nivel de stock bajo (stock <= stock_minimo), no se
                // permite registrar una salida hasta que se reabastezca.
                if ($material->stock <= $material->stock_minimo) {
                    throw ValidationException::withMessages([
                        'material_id' => "{$material->nombre} ya está en nivel de stock bajo (stock: {$material->stock}, mínimo: {$material->stock_minimo}). No se puede registrar una salida hasta reabastecerlo.",
                    ]);
                }

                if ($datos['cantidad'] > $material->stock) {
                    throw ValidationException::withMessages([
                        'cantidad' => "Stock insuficiente. Stock actual de {$material->nombre}: {$material->stock}.",
                    ]);
                }
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

    public static function registrarSalidaPorEntrega(Material $material, int $cantidad, int $usuarioId, int $salidaId): void
    {
        $material->refresh();
        $material->stock = max(0, $material->stock - $cantidad);
        $material->save();

        MovimientoInventario::create([
            'material_id' => $material->id,
            'tipo' => MovimientoInventario::TIPO_SALIDA,
            'cantidad' => $cantidad,
            'motivo' => 'Salida de materiales (vale de entrega)',
            'fecha' => now(),
            'usuario_id' => $usuarioId,
            'salida_id' => $salidaId,
            'stock_resultante' => $material->stock,
        ]);
    }

    public function exportarPdf(Request $request)
    {
        $query = MovimientoInventario::with(['material', 'usuario'])->orderByDesc('fecha');

        if ($materialId = $request->get('material_id')) {
            $query->where('material_id', $materialId);
        }

        if ($tipo = $request->get('tipo')) {
            $query->where('tipo', $tipo);
        }

        $movimientos = $query->get();

        $pdf = Pdf::loadView('inventario.movimientos.pdf', [
            'movimientos' => $movimientos,
            'fecha' => now(),
            'tipo' => $tipo ?? null,
        ]);

        return $pdf->download('reporte-movimientos-inventario.pdf');
    }
}