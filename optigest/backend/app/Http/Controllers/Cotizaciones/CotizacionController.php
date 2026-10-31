<?php

namespace App\Http\Controllers\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\CatalogoServicio;
use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\Ticket;
use App\Models\TicketGasto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::with([
            'cotizador',
            'ticket',
            'cliente',
        ])->orderByDesc('fecha');

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
        return view('cotizaciones.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $datos = $this->validarDatos($request);

        $lineasMateriales = collect($datos['materiales'] ?? [])
            ->filter(fn ($linea) => !empty($linea['id']))
            ->values();

        $lineasServicios = collect($datos['servicios'] ?? [])
            ->filter(fn ($linea) => !empty($linea['id']))
            ->values();

        $this->validarContenido(
            $datos['ticket_id'] ?? null,
            $lineasMateriales,
            $lineasServicios
        );

        $cotizacion = DB::transaction(function () use (
            $datos,
            $request,
            $lineasMateriales,
            $lineasServicios
        ) {
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

            $this->guardarLineas(
                $cotizacion,
                $lineasMateriales,
                $lineasServicios
            );

            $cotizacion->recalcularTotales();

            return $cotizacion;
        });

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with(
                'status',
                "Cotización {$cotizacion->codigo} creada en estado borrador."
            );
    }

    public function show(Cotizacion $cotizacion)
    {
        return view('cotizaciones.show', [
            'cotizacion' => $cotizacion->load([
                'detalles.material',
                'servicios',
                'gastos',
                'cotizador',
                'ticket',
                'cliente',
            ]),
        ]);
    }

    public function edit(Cotizacion $cotizacion)
    {
        $this->asegurarBorrador($cotizacion);

        $cotizacion->load([
            'detalles',
            'servicios',
        ]);

        $datos = $this->datosFormulario();
        $datos['cotizacion'] = $cotizacion;

        return view('cotizaciones.edit', $datos);
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $datos = $this->validarDatos($request);

        $lineasMateriales = collect($datos['materiales'] ?? [])
            ->filter(fn ($linea) => !empty($linea['id']))
            ->values();

        $lineasServicios = collect($datos['servicios'] ?? [])
            ->filter(fn ($linea) => !empty($linea['id']))
            ->values();

        $this->validarContenido(
            $datos['ticket_id'] ?? null,
            $lineasMateriales,
            $lineasServicios
        );

        DB::transaction(function () use (
            $cotizacion,
            $datos,
            $request,
            $lineasMateriales,
            $lineasServicios
        ) {
            $cotizacion = Cotizacion::whereKey($cotizacion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->asegurarBorrador($cotizacion);

            // Desvincular gastos cobrables anteriores sin eliminarlos.
            $cotizacion->gastos()
                ->where('cobrar_al_cliente', true)
                ->update([
                    'cotizacion_id' => null,
                ]);

            $cotizacion->update([
                'cliente_id' => $datos['cliente_id'],
                'ticket_id' => $datos['ticket_id'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'iva_aplicado' => $request->boolean('aplicar_iva'),
            ]);

            $cotizacion->detalles()->delete();
            $cotizacion->servicios()->delete();

            $this->guardarLineas(
                $cotizacion,
                $lineasMateriales,
                $lineasServicios
            );

            $cotizacion->recalcularTotales();
        });

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with(
                'status',
                "Cotización {$cotizacion->codigo} actualizada correctamente."
            );
    }

    public function destroy(Cotizacion $cotizacion)
    {
        DB::transaction(function () use ($cotizacion) {
            $cotizacion = Cotizacion::whereKey($cotizacion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // No eliminar cotizaciones con movimientos de inventario.
            if ($cotizacion->movimientosInventario()->exists()) {
                throw ValidationException::withMessages([
                    'cotizacion' =>
                        'No se puede eliminar esta cotización porque tiene movimientos de inventario asociados.',
                ]);
            }

            // Conservar los gastos y sus comprobantes.
            $cotizacion->gastos()->update([
                'cotizacion_id' => null,
            ]);

            $cotizacion->delete();
        });

        return redirect()
            ->route('cotizaciones.index')
            ->with(
                'status',
                'Cotización eliminada correctamente.'
            );
    }

    public function aprobar(Request $request, Cotizacion $cotizacion)
    {
        if (!in_array(
            $cotizacion->estado,
            ['borrador', 'enviada'],
            true
        )) {
            return back()->withErrors([
                'cotizacion' =>
                    'Solo se pueden aprobar cotizaciones en borrador o enviadas.',
            ]);
        }

        DB::transaction(function () use ($cotizacion) {
            $cotizacion = Cotizacion::whereKey($cotizacion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array(
                $cotizacion->estado,
                ['borrador', 'enviada'],
                true
            )) {
                throw ValidationException::withMessages([
                    'cotizacion' =>
                        'El estado de la cotización cambió. Actualiza la página e inténtalo de nuevo.',
                ]);
            }

            $cotizacion->update([
                'estado' => 'aprobada',
            ]);
        });

        $faltantes = $cotizacion->detalles()
            ->with('material')
            ->get()
            ->filter(fn ($detalle) =>
                $detalle->material &&
                $detalle->material->stock < $detalle->cantidad
            )
            ->map(fn ($detalle) =>
                "{$detalle->material->nombre} (disponible: {$detalle->material->stock}, requerido: {$detalle->cantidad})"
            );

        $mensaje = "Cotización {$cotizacion->codigo} aprobada. El stock se descuenta al registrar la salida de materiales.";

        if ($faltantes->isNotEmpty()) {
            $mensaje .= ' Aviso: stock insuficiente para '
                . $faltantes->implode(', ')
                . '.';
        }

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with('status', $mensaje);
    }

    public function rechazar(Cotizacion $cotizacion)
    {
        if (!in_array(
            $cotizacion->estado,
            ['borrador', 'enviada'],
            true
        )) {
            return back()->withErrors([
                'cotizacion' =>
                    'Solo se pueden rechazar cotizaciones en borrador o enviadas.',
            ]);
        }

        DB::transaction(function () use ($cotizacion) {
            $cotizacion = Cotizacion::whereKey($cotizacion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array(
                $cotizacion->estado,
                ['borrador', 'enviada'],
                true
            )) {
                throw ValidationException::withMessages([
                    'cotizacion' =>
                        'El estado de la cotización cambió. Actualiza la página e inténtalo de nuevo.',
                ]);
            }

            $cotizacion->update([
                'estado' => 'rechazada',
            ]);

            $cotizacion->gastos()
                ->where('cobrar_al_cliente', true)
                ->update([
                    'cotizacion_id' => null,
                ]);
        });

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with(
                'status',
                "Cotización {$cotizacion->codigo} marcada como rechazada."
            );
    }

    public function revertir(Cotizacion $cotizacion)
    {
        DB::transaction(function () use ($cotizacion) {
            $cotizacion = Cotizacion::whereKey($cotizacion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array(
                $cotizacion->estado,
                ['aprobada', 'rechazada'],
                true
            )) {
                throw ValidationException::withMessages([
                    'cotizacion' =>
                        'Solo se pueden revertir cotizaciones aprobadas o rechazadas.',
                ]);
            }

            if ($cotizacion->movimientosInventario()->exists()) {
                throw ValidationException::withMessages([
                    'cotizacion' =>
                        'No se puede revertir esta cotización porque tiene movimientos de inventario asociados.',
                ]);
            }

            $cotizacion->update([
                'estado' => 'borrador',
            ]);
        });

        return redirect()
            ->route('cotizaciones.show', $cotizacion)
            ->with(
                'status',
                "La cotización {$cotizacion->codigo} volvió al estado borrador."
            );
    }

    public function exportarPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load([
            'detalles.material',
            'servicios',
            'gastos',
            'cotizador',
            'cliente',
        ]);

        $pdf = Pdf::loadView('cotizaciones.pdf', [
            'cotizacion' => $cotizacion,
        ]);

        return $pdf->download(
            "cotizacion-{$cotizacion->codigo}.pdf"
        );
    }

    private function datosFormulario(): array
    {
        $todosMateriales = Material::where('activo', true)
            ->with('proveedor')
            ->orderByRaw(
                "CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC"
            )
            ->get();

        $materialesBodega = [];
        $materialesExternos = [];

        foreach ($todosMateriales as $material) {
            $comparativa = $material->comparativaProveedores();

            $item = [
                'id' => $material->id,
                'codigo' => $material->codigo,
                'nombre' => $material->nombre,
                'stock' => $material->stock,
                'precio' => (float) $material->precio,
                'proveedor' =>
                    $comparativa['mas_barato']['proveedor'] ?? '',
                'precio_prov' =>
                    $comparativa['mas_barato']['precio'] ?? '',
            ];

            if ($material->en_bodega) {
                $materialesBodega[] = $item;
            } else {
                $materialesExternos[] = $item;
            }
        }

        return [
            'materialesBodega' => $materialesBodega,
            'materialesExternos' => $materialesExternos,

            'servicios' => CatalogoServicio::where('activo', true)
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get(),

            'ivaTasa' => config('optigest.iva', 0.12),

            'tickets' => Ticket::whereIn('estado', [
                'pendiente',
                'asignado',
                'en_proceso',
            ])
                ->with('cliente')
                ->orderByDesc('id')
                ->get(),

            'clientes' => Cliente::orderBy('nombre')->get(),
        ];
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'cliente_id' => [
                'required',
                'exists:clientes,id',
            ],

            'ticket_id' => [
                'nullable',
                'exists:tickets,id',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
                'not_regex:/[<>]/',
            ],

            'materiales' => [
                'nullable',
                'array',
            ],

            'materiales.*.id' => [
                'nullable',
                'required_with:materiales.*.cantidad',
                'exists:materiales,id',
            ],

            'materiales.*.cantidad' => [
                'nullable',
                'required_with:materiales.*.id',
                'integer',
                'min:1',
            ],

            'servicios' => [
                'nullable',
                'array',
            ],

            'servicios.*.id' => [
                'nullable',
                'required_with:servicios.*.cantidad',
                'exists:catalogo_servicios,id',
            ],

            'servicios.*.cantidad' => [
                'nullable',
                'required_with:servicios.*.id',
                'numeric',
                'min:0.01',
                'max:9999',
            ],

            'aplicar_iva' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    private function validarContenido(
        ?int $ticketId,
        $lineasMateriales,
        $lineasServicios
    ): void {
        $tieneGastos = $ticketId
            && TicketGasto::where('ticket_id', $ticketId)
                ->where('cobrar_al_cliente', true)
                ->whereNull('cotizacion_id')
                ->exists();

        if (
            $lineasMateriales->isEmpty() &&
            $lineasServicios->isEmpty() &&
            !$tieneGastos
        ) {
            throw ValidationException::withMessages([
                'materiales' =>
                    'Agrega al menos un material, un servicio o selecciona un ticket con gastos cobrables disponibles.',
            ]);
        }
    }

    private function guardarLineas(
        Cotizacion $cotizacion,
        $lineasMateriales,
        $lineasServicios
    ): void {
        foreach ($lineasMateriales as $linea) {
            $material = Material::where('activo', true)
                ->findOrFail($linea['id']);

            $cantidad = (int) $linea['cantidad'];

            $cotizacion->detalles()->create([
                'material_id' => $material->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $material->precio,
                'subtotal' => round(
                    (float) $material->precio * $cantidad,
                    2
                ),
            ]);
        }

        foreach ($lineasServicios as $linea) {
            $servicio = CatalogoServicio::where('activo', true)
                ->findOrFail($linea['id']);

            $cantidad = (float) $linea['cantidad'];

            $cotizacion->servicios()->create([
                'servicio_id' => $servicio->id,
                'descripcion' => $servicio->nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => $servicio->precio_estandar,
                'subtotal' => round(
                    (float) $servicio->precio_estandar * $cantidad,
                    2
                ),
            ]);
        }
    }

    private function asegurarBorrador(Cotizacion $cotizacion): void
    {
        if ($cotizacion->estado !== 'borrador') {
            throw ValidationException::withMessages([
                'cotizacion' =>
                    'Solo se pueden modificar cotizaciones en estado borrador.',
            ]);
        }
    }
}