<?php

namespace App\Http\Controllers\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventario\MovimientoInventarioController;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with(['cotizador', 'ticket', 'cliente'])->orderByDesc('fecha');

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        return view('cotizaciones.index', [
            'cotizaciones' => $query->paginate(15)->withQueryString(),
            'estados' => Cotizacion::ESTADOS,
        ]);
    }

    public function create()
    {
        $materiales = Material::where('activo', true)->with('proveedor')->orderBy('nombre')->get();

        // Para cada material, calcula cuál es el proveedor más barato
        // (comparando el proveedor de catálogo contra los registrados
        // en precios_proveedor_material). Esto es lo que pidió la
        // empresa: sugerir al cotizador el proveedor que más conviene.
        $mejoresProveedores = $materiales->mapWithKeys(function ($material) {
            return [$material->id => $material->mejorProveedor()];
        });

        return view('cotizaciones.create', [
            'materiales' => $materiales,
            'mejoresProveedores' => $mejoresProveedores,
            'tickets' => Ticket::whereIn('estado', ['pendiente', 'asignado', 'en_proceso'])->with('cliente')->orderByDesc('id')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'observaciones' => ['nullable', 'string'],
            'materiales' => ['required', 'array', 'min:1'],
            'materiales.*.id' => ['required', 'exists:materiales,id'],
            'materiales.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $cotizacion = DB::transaction(function () use ($datos, $request) {
            $cotizacion = Cotizacion::create([
                'codigo' => Cotizacion::generarCodigo(),
                'cliente_id' => $datos['cliente_id'],
                'ticket_id' => $datos['ticket_id'] ?? null,
                'cotizador_id' => $request->user()->id,
                'estado' => 'borrador',
                'fecha' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($datos['materiales'] as $linea) {
                $material = Material::findOrFail($linea['id']);

                $cotizacion->detalles()->create([
                    'material_id' => $material->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $material->precio,
                    'subtotal' => $material->precio * $linea['cantidad'],
                ]);
            }

            $cotizacion->recalcularTotales();

            return $cotizacion;
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotizacion {$cotizacion->codigo} creada en estado borrador.");
    }

    public function show(Cotizacion $cotizacion)
    {
        return view('cotizaciones.show', ['cotizacion' => $cotizacion->load(['detalles.material', 'cotizador', 'ticket', 'cliente'])]);
    }

    public function aprobar(Request $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'aprobada') {
            return back()->with('status', 'Esta cotizacion ya fue aprobada anteriormente.');
        }

        DB::transaction(function () use ($cotizacion, $request) {
            foreach ($cotizacion->detalles as $detalle) {
                $material = Material::lockForUpdate()->findOrFail($detalle->material_id);

                if ($material->stock < $detalle->cantidad) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuficiente de {$material->nombre} para aprobar la cotizacion (disponible: {$material->stock}, requerido: {$detalle->cantidad}).",
                    ]);
                }

                MovimientoInventarioController::registrarSalidaPorCotizacion(
                    $material, $detalle->cantidad, $request->user()->id, $cotizacion->id
                );
            }

            $cotizacion->update(['estado' => 'aprobada']);
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotizacion {$cotizacion->codigo} aprobada. Stock de inventario actualizado automaticamente.");
    }

    public function rechazar(Cotizacion $cotizacion)
    {
        $cotizacion->update(['estado' => 'rechazada']);

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotizacion {$cotizacion->codigo} marcada como rechazada.");
    }

    public function exportarPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['detalles.material', 'cotizador', 'cliente']);
        $pdf = Pdf::loadView('cotizaciones.pdf', ['cotizacion' => $cotizacion]);

        return $pdf->download("cotizacion-{$cotizacion->codigo}.pdf");
    }
}