<?php

namespace App\Http\Controllers\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventario\MovimientoInventarioController;
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
        $materiales = Material::where('activo', true)->with('proveedor')->orderByRaw("CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC")->get();

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
            'servicios' => CatalogoServicio::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get(),
            'ivaTasa' => config('optigest.iva'),
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

        // Solo cuentan las líneas con algo seleccionado.
        $lineasMateriales = collect($datos['materiales'] ?? [])->filter(fn ($l) => ! empty($l['id']))->values();
        $lineasServicios = collect($datos['servicios'] ?? [])->filter(fn ($l) => ! empty($l['id']))->values();

        // Con ticket, los gastos adicionales cobrables también cuentan como contenido.
        $tieneGastos = ! empty($datos['ticket_id'])
            && \App\Models\TicketGasto::where('ticket_id', $datos['ticket_id'])
                ->where('cobrar_al_cliente', true)->whereNull('cotizacion_id')->exists();

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

                $cotizacion->detalles()->create([
                    'material_id' => $material->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $material->precio,
                    'subtotal' => $material->precio * $linea['cantidad'],
                ]);
            }

            // Mano de obra: el precio sale SIEMPRE del catálogo (estandarizado),
            // nunca de lo que llegue en el formulario.
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
            ->with('status', "Cotizacion {$cotizacion->codigo} creada en estado borrador.");
    }

    public function show(Cotizacion $cotizacion)
    {
        return view('cotizaciones.show', ['cotizacion' => $cotizacion->load(['detalles.material', 'servicios', 'gastos', 'cotizador', 'ticket', 'cliente'])]);
    }

    public function aprobar(Request $request, Cotizacion $cotizacion)
    {
        if ($cotizacion->estado === 'aprobada') {
            return back()->with('status', 'Esta cotizacion ya fue aprobada anteriormente.');
        }

        // El stock NO se descuenta al aprobar: sale de bodega solo cuando se
        // registra la salida de materiales (la entrega física al técnico).
        // Así un mismo material nunca se descuenta dos veces.
        $cotizacion->update(['estado' => 'aprobada']);

        // Aviso (no bloquea): materiales cuyo stock actual no alcanza para la cotización.
        $faltantes = $cotizacion->detalles()->with('material')->get()
            ->filter(fn ($d) => $d->material && $d->material->stock < $d->cantidad)
            ->map(fn ($d) => "{$d->material->nombre} (disponible: {$d->material->stock}, requerido: {$d->cantidad})");

        $mensaje = "Cotizacion {$cotizacion->codigo} aprobada. El stock se descuenta al registrar la salida de materiales.";

        if ($faltantes->isNotEmpty()) {
            $mensaje .= ' Aviso: stock insuficiente para '.$faltantes->implode(', ').'.';
        }

        return redirect()->route('cotizaciones.show', $cotizacion)->with('status', $mensaje);
    }

    public function rechazar(Cotizacion $cotizacion)
    {
        $cotizacion->update(['estado' => 'rechazada']);

        // Libera los gastos que esta cotización iba a cobrar, para que otra
        // cotización del mismo ticket pueda incluirlos.
        $cotizacion->gastos()->update(['cotizacion_id' => null]);

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('status', "Cotizacion {$cotizacion->codigo} marcada como rechazada.");
    }

    public function exportarPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['detalles.material', 'servicios', 'gastos', 'cotizador', 'cliente']);
        $pdf = Pdf::loadView('cotizaciones.pdf', ['cotizacion' => $cotizacion]);

        return $pdf->download("cotizacion-{$cotizacion->codigo}.pdf");
    }
}