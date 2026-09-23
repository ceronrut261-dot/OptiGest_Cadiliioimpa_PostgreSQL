<?php

namespace App\Http\Controllers\Salidas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventario\MovimientoInventarioController;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\SalidaMaterial;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalidaMaterialController extends Controller
{
    public function index(Request $request)
    {
        return view('salidas.index', [
            'salidas' => SalidaMaterial::with(['usuario'])
                ->orderByDesc('fecha')
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('salidas.create', [
            'materiales' => Material::where('activo', true)
                ->orderBy('nombre')
                ->get(),

            'tickets' => Ticket::orderByDesc('id')
                ->limit(50)
                ->get(),

            'clientes' => Cliente::orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'proyecto' => ['required', 'string', 'max:255'],
            'persona_recibe' => ['required', 'string', 'max:255'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'observaciones' => ['nullable', 'string'],
            'falta_comprar' => ['nullable', 'string'],

            'materiales' => ['required', 'array', 'min:1'],
            'materiales.*.id' => ['required', 'exists:materiales,id'],
            'materiales.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $salida = DB::transaction(function () use ($datos, $request) {

            $salida = SalidaMaterial::create([
                'codigo' => SalidaMaterial::generarCodigo(),
                'usuario_id' => $request->user()->id,
                'proyecto' => $datos['proyecto'],
                'persona_recibe' => $datos['persona_recibe'],
                'ticket_id' => $datos['ticket_id'] ?? null,
                'fecha' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
                'falta_comprar' => $datos['falta_comprar'] ?? null,
            ]);

            foreach ($datos['materiales'] as $linea) {

                $material = Material::lockForUpdate()
                    ->findOrFail($linea['id']);

                if ($linea['cantidad'] > $material->stock) {
                    throw ValidationException::withMessages([
                        'materiales' => "Stock insuficiente de {$material->nombre} (disponible: {$material->stock}).",
                    ]);
                }

                $total = $material->precio * $linea['cantidad'];

                $salida->detalles()->create([
                    'material_id' => $material->id,
                    'descripcion' => $material->nombre,
                    'cantidad' => $linea['cantidad'],
                    'valor_unitario' => $material->precio,
                    'total' => $total,
                ]);

                MovimientoInventarioController::registrarSalidaPorEntrega(
                    $material,
                    $linea['cantidad'],
                    $request->user()->id,
                    $salida->id
                );
            }

            $salida->recalcularTotal();

            return $salida;
        });

        return redirect()
            ->route('salidas.pdf', $salida)
            ->with(
                'status',
                "Salida {$salida->codigo} registrada. Descarga el vale para las firmas."
            );
    }

    public function exportarPdf(SalidaMaterial $salida)
    {
        $salida->load([
            'detalles.material',
            'usuario',
            'ticket'
        ]);

        $pdf = Pdf::loadView('salidas.pdf', [
            'salida' => $salida
        ]);

        return $pdf->download("salida-{$salida->codigo}.pdf");
    }
}