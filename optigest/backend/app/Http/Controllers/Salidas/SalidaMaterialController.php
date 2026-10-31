<?php

namespace App\Http\Controllers\Salidas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventario\MovimientoInventarioController;
use App\Models\MovimientoInventario;
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
            'salidas' => SalidaMaterial::with([
                'usuario',
                'salidaOriginal',
            ])
                ->orderByDesc('fecha')
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('salidas.create', [
            'materiales' => Material::where('activo', true)
                ->where('en_bodega', true)
                ->orderByRaw(
                    "CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC"
                )
                ->get(),

            'tickets' => Ticket::orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }

    /**
     * Registrar una salida normal.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'proyecto' => [
                'required', 'string', 'max:150', 'not_regex:/[<>]/',
            ],
            'persona_recibe' => [
                'required', 'string', 'max:100',
                "regex:/^[\\pL\\s.'\\-]+$/u",
            ],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'observaciones' => [
                'nullable', 'string', 'max:1000', 'not_regex:/[<>]/',
            ],
            'falta_comprar' => [
                'nullable', 'string', 'max:1000', 'not_regex:/[<>]/',
            ],
            'materiales' => ['required', 'array', 'min:1', 'max:100'],
            'materiales.*.id' => [
                'required', 'integer', 'exists:materiales,id',
            ],
            'materiales.*.cantidad' => [
                'required', 'integer', 'min:1', 'max:100000',
            ],
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
                'estado_entrega' => 'pendiente',
                'confirmado_por' => null,
                'confirmado_en' => null,
                'observaciones_entrega' => null,
                'salida_original_id' => null,
            ]);

            foreach ($datos['materiales'] as $linea) {
                $material = Material::where('en_bodega', true)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->findOrFail($linea['id']);

                if ($linea['cantidad'] > $material->stock) {
                    throw ValidationException::withMessages([
                        'materiales' =>
                            "Stock insuficiente de {$material->nombre} " .
                            "(disponible: {$material->stock}).",
                    ]);
                }

                $total = $material->precio * $linea['cantidad'];

                $salida->detalles()->create([
                    'material_id' => $material->id,
                    'descripcion' => $material->nombre,
                    'cantidad' => $linea['cantidad'],
                    'cantidad_entregada' => 0,
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

        return redirect()->route('salidas.pdf', $salida)
            ->with(
                'status',
                "Salida {$salida->codigo} registrada. " .
                'La entrega queda pendiente de confirmación.'
            );
    }

    /**
     * Mostrar el formulario de creación de un vale complementario.
     */
    public function crearComplementaria(
        Request $request,
        SalidaMaterial $salida
    ) {
        $this->autorizarConfirmacion($request);

        $salida->load(['detalles.material', 'salidasComplementarias']);

        if ($salida->salida_original_id !== null) {
            return redirect()->route('salidas.index')
                ->with('status', 'Solo puedes generar complementos desde un vale original.');
        }

        if ($salida->estado_entrega !== 'confirmada') {
            return redirect()->route('salidas.index')
                ->with('status', 'Primero debes confirmar la entrega del vale original.');
        }

        if ($salida->salidasComplementarias()
            ->where('estado_entrega', 'pendiente')
            ->exists()) {
            return redirect()->route('salidas.index')
                ->with('status', 'Ya existe un vale complementario pendiente de confirmación.');
        }

        $pendientes = $this->obtenerMaterialesPendientes($salida);

        if ($pendientes->isEmpty()) {
            return redirect()->route('salidas.index')
                ->with('status', 'Este vale no tiene materiales pendientes de entregar.');
        }

        return view('salidas.complementaria', [
            'salida' => $salida,
            'pendientes' => $pendientes,
            'tickets' => Ticket::orderByDesc('id')->limit(50)->get(),
        ]);
    }

    /**
     * Crear y registrar un vale complementario.
     */
    public function guardarComplementaria(
        Request $request,
        SalidaMaterial $salida
    ) {
        $this->autorizarConfirmacion($request);

        $datos = $request->validate([
            'materiales' => ['required', 'array', 'min:1', 'max:100'],
            'materiales.*.id' => [
                'required', 'integer', 'exists:materiales,id',
            ],
            'materiales.*.cantidad' => [
                'required', 'integer', 'min:1', 'max:100000',
            ],
            'observaciones' => [
                'nullable', 'string', 'max:1000', 'not_regex:/[<>]/',
            ],
            'falta_comprar' => [
                'nullable', 'string', 'max:1000', 'not_regex:/[<>]/',
            ],
        ]);

        $complementaria = DB::transaction(function () use (
            $request,
            $salida,
            $datos
        ) {
            $original = SalidaMaterial::whereKey($salida->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($original->salida_original_id !== null) {
                throw ValidationException::withMessages([
                    'materiales' => 'No puedes generar complementos desde otro vale complementario.',
                ]);
            }

            if ($original->estado_entrega !== 'confirmada') {
                throw ValidationException::withMessages([
                    'materiales' => 'El vale original debe estar confirmado.',
                ]);
            }

            if ($original->salidasComplementarias()
                ->where('estado_entrega', 'pendiente')
                ->exists()) {
                throw ValidationException::withMessages([
                    'materiales' => 'Ya existe un vale complementario pendiente de confirmación.',
                ]);
            }

            $pendientes = $this->obtenerMaterialesPendientes($original)
                ->keyBy('material_id');

            $complementaria = SalidaMaterial::create([
                'codigo' => SalidaMaterial::generarCodigo(),
                'usuario_id' => $request->user()->id,
                'proyecto' => $original->proyecto,
                'persona_recibe' => $original->persona_recibe,
                'ticket_id' => $original->ticket_id,
                'fecha' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
                'falta_comprar' => $datos['falta_comprar'] ?? null,
                'estado_entrega' => 'pendiente',
                'confirmado_por' => null,
                'confirmado_en' => null,
                'observaciones_entrega' => null,
                'salida_original_id' => $original->id,
            ]);

            $materialesProcesados = [];

            foreach ($datos['materiales'] as $linea) {
                $materialId = (int) $linea['id'];
                $cantidad = (int) $linea['cantidad'];

                if (isset($materialesProcesados[$materialId])) {
                    throw ValidationException::withMessages([
                        'materiales' => 'No repitas el mismo material en varias líneas.',
                    ]);
                }

                $materialesProcesados[$materialId] = true;

                if (!$pendientes->has($materialId)) {
                    throw ValidationException::withMessages([
                        'materiales' => 'Uno de los materiales seleccionados no tiene cantidades pendientes.',
                    ]);
                }

                $pendiente = $pendientes->get($materialId);

                if ($cantidad > $pendiente->cantidad_pendiente) {
                    throw ValidationException::withMessages([
                        'materiales' =>
                            "Solo quedan {$pendiente->cantidad_pendiente} " .
                            "unidades pendientes de {$pendiente->descripcion}.",
                    ]);
                }

                $material = Material::where('en_bodega', true)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->findOrFail($materialId);

                if ($cantidad > $material->stock) {
                    throw ValidationException::withMessages([
                        'materiales' =>
                            "Stock insuficiente de {$material->nombre} " .
                            "(disponible: {$material->stock}).",
                    ]);
                }

                $total = $material->precio * $cantidad;

                $complementaria->detalles()->create([
                    'material_id' => $material->id,
                    'descripcion' => $material->nombre,
                    'cantidad' => $cantidad,
                    'cantidad_entregada' => 0,
                    'valor_unitario' => $material->precio,
                    'total' => $total,
                ]);

                MovimientoInventarioController::registrarSalidaPorEntrega(
                    $material,
                    $cantidad,
                    $request->user()->id,
                    $complementaria->id
                );
            }

            $complementaria->recalcularTotal();

            return $complementaria;
        });

        return redirect()->route('salidas.pdf', $complementaria)
            ->with(
                'status',
                "Vale complementario {$complementaria->codigo} registrado correctamente."
            );
    }

    /**
     * Calcula las cantidades que todavía no se han entregado.
     *
     * Se toma lo recibido en el vale original y se descuentan
     * las cantidades entregadas en los vales complementarios confirmados.
     */
    private function obtenerMaterialesPendientes(SalidaMaterial $salida)
    {
        $salida->loadMissing('detalles');

        $solicitado = [];

        foreach ($salida->detalles as $detalle) {
            $id = (int) $detalle->material_id;

            if (!isset($solicitado[$id])) {
                $solicitado[$id] = [
                    'material_id' => $id,
                    'descripcion' => $detalle->descripcion,
                    'cantidad_solicitada' => 0,
                    'cantidad_entregada' => 0,
                ];
            }

            $solicitado[$id]['cantidad_solicitada'] += (int) $detalle->cantidad;
            $solicitado[$id]['cantidad_entregada'] += (int) $detalle->cantidad_entregada;
        }

        $complementarias = $salida->salidasComplementarias()
            ->where('estado_entrega', 'confirmada')
            ->with('detalles')
            ->get();

        foreach ($complementarias as $complementaria) {
            foreach ($complementaria->detalles as $detalle) {
                $id = (int) $detalle->material_id;

                if (isset($solicitado[$id])) {
                    $solicitado[$id]['cantidad_entregada'] +=
                        (int) $detalle->cantidad_entregada;
                }
            }
        }

        $resultado = collect();

        foreach ($solicitado as $item) {
            $pendiente = $item['cantidad_solicitada'] -
                $item['cantidad_entregada'];

            if ($pendiente > 0) {
                $material = Material::find($item['material_id']);

                if ($material) {
                    $resultado->push((object) [
                        'material_id' => $item['material_id'],
                        'descripcion' => $item['descripcion'],
                        'cantidad_solicitada' => $item['cantidad_solicitada'],
                        'cantidad_entregada' => $item['cantidad_entregada'],
                        'cantidad_pendiente' => $pendiente,
                        'stock' => $material->stock,
                        'precio' => $material->precio,
                    ]);
                }
            }
        }

        return $resultado;
    }

    /**
     * Muestra el formulario para confirmar las cantidades recibidas.
     */
    public function mostrarConfirmacion(
        Request $request,
        SalidaMaterial $salida
    ) {
        $this->autorizarConfirmacion($request);

        $salida->load([
            'detalles.material',
            'usuario',
            'ticket',
            'salidaOriginal',
        ]);

        if ($salida->estado_entrega !== 'pendiente') {
            return redirect()->route('salidas.index')
                ->with('status', 'Este vale ya fue confirmado.');
        }

        return view('salidas.confirmar', compact('salida'));
    }

    /**
     * Confirma la entrega y devuelve al inventario lo no entregado.
     */
    public function confirmar(
        Request $request,
        SalidaMaterial $salida
    ) {
        $this->autorizarConfirmacion($request);

        $datos = $request->validate([
            'entregadas' => ['required', 'array', 'min:1'],
            'entregadas.*' => [
                'required', 'integer', 'min:0', 'max:100000',
            ],
            'observaciones_entrega' => [
                'nullable', 'string', 'max:1000', 'not_regex:/[<>]/',
            ],
        ]);

        DB::transaction(function () use ($request, $salida, $datos) {
            $salida = SalidaMaterial::whereKey($salida->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($salida->estado_entrega !== 'pendiente') {
                throw ValidationException::withMessages([
                    'entregadas' =>
                        'Este vale ya fue confirmado y no puede procesarse otra vez.',
                ]);
            }

            $detalles = $salida->detalles()
                ->lockForUpdate()
                ->get();

            if ($detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'entregadas' => 'El vale no contiene materiales.',
                ]);
            }

            if (
                count($datos['entregadas']) !== $detalles->count()
                || $detalles->contains(
                    fn ($detalle) =>
                        !array_key_exists(
                            (string) $detalle->id,
                            $datos['entregadas']
                        )
                )
            ) {
                throw ValidationException::withMessages([
                    'entregadas' =>
                        'Indica la cantidad recibida de cada material del vale.',
                ]);
            }

            foreach ($detalles as $detalle) {
                $cantidadSolicitada = (int) $detalle->cantidad;
                $cantidadEntregada = (int) $datos['entregadas'][$detalle->id];

                if ($cantidadEntregada > $cantidadSolicitada) {
                    throw ValidationException::withMessages([
                        "entregadas.{$detalle->id}" =>
                            "No puedes registrar más de {$cantidadSolicitada} " .
                            'unidades para este material.',
                    ]);
                }

                $diferencia = $cantidadSolicitada - $cantidadEntregada;

                $detalle->cantidad_entregada = $cantidadEntregada;
                $detalle->save();

                if ($diferencia > 0) {
                    $material = Material::whereKey($detalle->material_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $material->stock += $diferencia;
                    $material->save();

                    MovimientoInventario::create([
                        'material_id' => $material->id,
                        'tipo' => MovimientoInventario::TIPO_ENTRADA,
                        'cantidad' => $diferencia,
                        'motivo' =>
                            "Devolución por entrega parcial del vale {$salida->codigo}",
                        'fecha' => now(),
                        'usuario_id' => $request->user()->id,
                        'salida_id' => $salida->id,
                        'stock_resultante' => $material->stock,
                    ]);
                }
            }

            $salida->estado_entrega = 'confirmada';
            $salida->confirmado_por = $request->user()->id;
            $salida->confirmado_en = now();
            $salida->observaciones_entrega =
                $datos['observaciones_entrega'] ?? null;

            $salida->save();
        });

        return redirect()->route('salidas.index')
            ->with('status', "Entrega {$salida->codigo} confirmada correctamente.");
    }

    private function autorizarConfirmacion(Request $request): void
    {
        $usuario = $request->user();

        abort_unless(
            $usuario && $usuario->hasAnyRole(['administrador', 'tecnico']),
            403,
            'Solo el administrador o un técnico pueden confirmar entregas.'
        );
    }

    public function exportarPdf(SalidaMaterial $salida)
    {
        $salida->load([
            'detalles.material',
            'usuario',
            'ticket',
            'salidaOriginal',
        ]);

        $pdf = Pdf::loadView('salidas.pdf', [
            'salida' => $salida,
        ]);

        return $pdf->download("salida-{$salida->codigo}.pdf");
    }
}
