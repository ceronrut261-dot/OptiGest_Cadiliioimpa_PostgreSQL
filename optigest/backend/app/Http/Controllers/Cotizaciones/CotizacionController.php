<?php

namespace App\Http\Controllers\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\CatalogoServicio;
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
        $todosMateriales = Material::where('activo', true)
            ->with('proveedor')
            ->orderByRaw("CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC")
            ->get();

        // Estructuración de datos para evitar llamadas a métodos dentro de la vista Blade
        $materialesBodega = [];
        $materialesExternos = [];

        foreach ($todosMateriales as $mat) {
            $comp = $mat->comparativaProveedores();
            $item = [
                'id' => $mat->id,
                'codigo' => $mat->codigo,
                'nombre' => $mat->nombre,
                'stock' => $mat->stock,
                'precio' => (float) $mat->precio,
                'proveedor' => $comp['mas_barato']['proveedor'] ?? '',
                'precio_prov' => $comp['mas_barato']['precio'] ?? '',
            ];

            if ($mat->en_bodega) {
                $materialesBodega[] = $item;
            } else {
                $materialesExternos[] = $item;
            }
        }

        return view('cotizaciones.create', [
            'materialesBodega' => $materialesBodega,
            'materialesExternos' => $materialesExternos,
            'servicios' => CatalogoServicio::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get(),
            'ivaTasa' => config('optigest.iva', 0.12),
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
            'materiales' => ['nullable', 'array'],
            'materiales.*.id' => ['nullable', 'exists:materiales,id'],
            'materiales.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'servicios' => ['nullable', 'array'],
            'servicios.*.id' => ['nullable', 'exists:catalogo_servicios,id'],
            'servicios.*.cantidad' => ['nullable', 'numeric', 'min:0.01', 'max:9999'],
            'aplicar_iva' => ['nullable', 'boolean'],
        ]);

        $lineasMateriales = collect($datos['materiales'] ?? [])->filter(fn ($l) => ! empty($l['id']))->values();
        $lineasServicios = collect($datos['servicios'] ?? [])->filter(fn ($l) => ! empty($l['id']))->values();

        $tieneGastos = ! empty($datos['ticket_id'])
            && \App\Models\TicketGasto::where('ticket_id', $datos['ticket_id'])
                ->where('cobrar_al_cliente', true)
                ->whereNull('cotizacion_id')
                ->exists();

        if ($lineasMateriales->isEmpty() && $lineasServicios->isEmpty() && ! $tieneGastos) {
            throw ValidationException::withMessages([
                'materiales' => 'Agrega al menos un material o un servicio de mano de obra.',
            ]);
        }

        $cotizacion = DB::transaction(function () use ($datos, $request, $lineasMateriales, $lineasServicios) {
            $cotizacion = Cotizacion::create([
                'codigo' => Cotizacion::generarCodigo(),
                'cliente_id' => $datos['cliente_id'],
                'ticket_id' => $datos['ticket_id'] ?? null,
                'cotizador_id' => $request->user()->id,
                'estado' => 'borrador',
                'fecha' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
                'iva_aplicado' => $request->boolean('aplicar_iva'),
            ]);

            foreach ($lineasMateriales as $linea) {
                $material = Material::findOrFail($linea['id']);
                $cantidad = (int) $linea['cantidad'];

                $cotizacion->detalles()->create([
                    'material_id' => $material->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $material->precio,
                    'subtotal' => round($material->precio * $cantidad, 2),
                ]);
            }

            foreach ($lineasServicios as $linea) {
                $servicio = CatalogoServicio::findOrFail($linea['id']);
                $cantidad = (float) ($linea['cantidad'] ?? 1);

                $cotizacion->servicios()->create([
                    'servicio_id' => $servicio->id,
                    'descripcion' => $servicio->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $servicio->precio_estandar,
                    'subtotal' => round($servicio->precio_estandar * $cantidad, 2),
                ]);
            }

            $cotizacion->recalcularTotales();

            return $cotizacion;
        });

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotización {$cotizacion->codigo} creada en estado borrador.");
    }

    public function show(Cotizacion $cotizacion)
    {
        return view('cotizaciones.show', [
            'cotizacion' => $cotizacion->load(['detalles.material', 'servicios', 'gastos', 'cotizador', 'ticket', 'cliente'])
        ]);
    }

    public function aprobar(Request $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'aprobada') {
            return back()->with('status', 'Esta cotización ya fue aprobada anteriormente.');
        }

        $cotizacion->update(['estado' => 'aprobada']);

        $faltantes = $cotizacion->detalles()->with('material')->get()
            ->filter(fn ($d) => $d->material && $d->material->stock < $d->cantidad)
            ->map(fn ($d) => "{$d->material->nombre} (disponible: {$d->material->stock}, requerido: {$d->cantidad})");

        $mensaje = "Cotización {$cotizacion->codigo} aprobada. El stock se descuenta al registrar la salida de materiales.";

        if ($faltantes->isNotEmpty()) {
            $mensaje .= ' Aviso: stock insuficiente para '.$faltantes->implode(', ').'.';
        }

        return redirect()->route('cotizaciones.show', $cotizacion)->with('status', $mensaje);
    }

    public function rechazar(Cotizacion $cotizacion)
    {
        $cotizacion->update(['estado' => 'rechazada']);
        $cotizacion->gastos()->update(['cotizacion_id' => null]);

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotización {$cotizacion->codigo} marcada como rechazada.");
    }

    public function exportarPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['detalles.material', 'servicios', 'gastos', 'cotizador', 'cliente']);
        $pdf = Pdf::loadView('cotizaciones.pdf', ['cotizacion' => $cotizacion]);

        return $pdf->download("cotizacion-{$cotizacion->codigo}.pdf");
    }
}