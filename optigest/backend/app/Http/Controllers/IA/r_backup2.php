<?php

namespace App\Http\Controllers\IA;

use App\Http\Controllers\Controller;
use App\Models\ConversacionIA;
use App\Models\Cotizacion;
use App\Models\Material;
use App\Models\MovimientoInventario;
use App\Models\Proveedor;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsistenteController extends Controller
{
    /**
     * Mostrar la pantalla del asistente.
     */
    public function index(Request $request)
    {
        return view('asistente.index', [
            'historial' => ConversacionIA::where(
                'usuario_id',
                $request->user()->id
            )
                ->orderByDesc('created_at')
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * Procesar una pregunta del usuario.
     */
    public function consultar(Request $request)
    {
        $datos = $request->validate([
            'pregunta' => ['required', 'string', 'max:1000'],
        ]);

        $pregunta = trim($datos['pregunta']);

        /*
         * Primero intentamos responder utilizando
         * directamente los datos de PostgreSQL.
         */
        $respuestaDirecta = $this->consultarBaseDatos($pregunta);

        if ($respuestaDirecta !== null) {
            $respuesta = $respuestaDirecta;
        } else {
            /*
             * Si la pregunta necesita interpretación,
             * utilizamos Ollama.
             */
            $contexto = $this->construirContextoOperativo();

            $respuesta = $this->consultarOllama(
                $contexto,
                $pregunta
            );
        }

        /*
         * Guardar la conversación.
         */
        $conversacion = ConversacionIA::create([
            'usuario_id' => $request->user()->id,
            'pregunta' => $pregunta,
            'respuesta' => $respuesta,
        ]);

        return back()->with(
            'ultimaRespuesta',
            $conversacion
        );
    }

    /**
     * Responde preguntas conocidas directamente
     * utilizando PostgreSQL.
     */
    private function consultarBaseDatos(string $pregunta): ?string
    {
        $p = mb_strtolower(trim($pregunta));

        /*
         * ==========================================
         * PROVEEDORES
         * ==========================================
         */

        if (
            str_contains($p, 'cuantos proveedores') ||
            str_contains($p, 'cuántos proveedores') ||
            str_contains($p, 'numero de proveedores') ||
            str_contains($p, 'número de proveedores')
        ) {
            $cantidad = Proveedor::where('activo', true)->count();

            return "Tienes {$cantidad} proveedores activos.";
        }

        if (
            str_contains($p, 'que proveedores tengo') ||
            str_contains($p, 'qué proveedores tengo') ||
            str_contains($p, 'proveedores tengo') ||
            str_contains($p, 'proveedores registrados') ||
            str_contains($p, 'proveedores disponibles')
        ) {
            $proveedores = Proveedor::where('activo', true)
                ->orderBy('nombre')
                ->pluck('nombre');

            if ($proveedores->isEmpty()) {
                return 'No tienes proveedores activos registrados.';
            }

            return 'Tienes los siguientes proveedores activos: ' .
                $proveedores->implode(', ') . '.';
        }

        /*
         * ==========================================
         * MATERIALES
         * ==========================================
         */

        if (
            str_contains($p, 'cuantos materiales') ||
            str_contains($p, 'cuántos materiales') ||
            str_contains($p, 'numero de materiales') ||
            str_contains($p, 'número de materiales')
        ) {
            $cantidad = Material::where('activo', true)->count();

            return "Tienes {$cantidad} materiales activos.";
        }

        /*
         * ==========================================
         * MATERIALES BAJO STOCK
         * ==========================================
         */

        if (
            str_contains($p, 'bajo stock') ||
            str_contains($p, 'bajo de stock') ||
            str_contains($p, 'stock minimo') ||
            str_contains($p, 'stock mínimo') ||
            str_contains($p, 'necesitan atencion') ||
            str_contains($p, 'necesitan atención') ||
            str_contains($p, 'deberia revisar') ||
            str_contains($p, 'debería revisar') ||
            str_contains($p, 'por agotarse') ||
            str_contains($p, 'reponer')
        ) {
            $materiales = Material::where('activo', true)
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->orderBy('stock')
                ->orderBy('nombre')
                ->get();

            if ($materiales->isEmpty()) {
                return 'El inventario se encuentra bien. No hay materiales bajo el stock mínimo.';
            }

            $cantidad = $materiales->count();

            $lista = $materiales->map(function ($material) {
                return "{$material->nombre} " .
                    "(stock: {$material->stock}, " .
                    "mínimo: {$material->stock_minimo})";
            })->implode(', ');

            return "Hay {$cantidad} materiales que necesitan atención: {$lista}.";
        }

        /*
         * ==========================================
         * ESTADO GENERAL DEL INVENTARIO
         * ==========================================
         */

        if (
            str_contains($p, 'inventario esta bien') ||
            str_contains($p, 'inventario está bien') ||
            str_contains($p, 'como esta el inventario') ||
            str_contains($p, 'cómo está el inventario') ||
            str_contains($p, 'estado del inventario') ||
            str_contains($p, 'estado general del inventario')
        ) {
            $totalMateriales = Material::where(
                'activo',
                true
            )->count();

            $bajoStock = Material::where(
                'activo',
                true
            )
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count();

            if ($bajoStock === 0) {
                return "El inventario se encuentra bien. " .
                    "Hay {$totalMateriales} materiales activos y " .
                    "ninguno está por debajo del stock mínimo.";
            }

            return "El inventario requiere atención. " .
                "Hay {$bajoStock} materiales de {$totalMateriales} " .
                "que están en o por debajo del stock mínimo.";
        }

        /*
         * ==========================================
         * TICKETS PENDIENTES
         * ==========================================
         */

        if (
            str_contains($p, 'tickets pendientes') ||
            str_contains($p, 'ticket pendiente')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'pendiente'
            )->count();

            return "Hay {$cantidad} tickets pendientes.";
        }

        /*
         * ==========================================
         * TICKETS ABIERTOS
         * ==========================================
         */

        if (
            str_contains($p, 'tickets abiertos') ||
            str_contains($p, 'tickets están abiertos') ||
            str_contains($p, 'tickets estan abiertos')
        ) {
            $cantidad = Ticket::whereNotIn(
                'estado',
                ['completado', 'cancelado']
            )->count();

            return "Hay {$cantidad} tickets abiertos.";
        }

        /*
         * ==========================================
         * TICKETS EN PROCESO
         * ==========================================
         */

        if (
            str_contains($p, 'tickets en proceso') ||
            str_contains($p, 'tickets están en proceso') ||
            str_contains($p, 'tickets estan en proceso')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'en_proceso'
            )->count();

            return "Hay {$cantidad} tickets en proceso.";
        }

        /*
         * ==========================================
         * TICKETS ASIGNADOS
         * ==========================================
         */

        if (
            str_contains($p, 'tickets asignados') ||
            str_contains($p, 'tickets están asignados') ||
            str_contains($p, 'tickets estan asignados')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'asignado'
            )->count();

            return "Hay {$cantidad} tickets asignados.";
        }

        /*
         * ==========================================
         * COTIZACIONES
         * ==========================================
         */

        if (
            str_contains($p, 'cuantas cotizaciones') ||
            str_contains($p, 'cuántas cotizaciones') ||
            str_contains($p, 'numero de cotizaciones') ||
            str_contains($p, 'número de cotizaciones')
        ) {
            $cantidad = Cotizacion::count();

            return "Tienes {$cantidad} cotizaciones registradas.";
        }

        /*
         * ==========================================
         * COTIZACIONES PENDIENTES
         * ==========================================
         */

        if (
            str_contains($p, 'cotizaciones pendientes') ||
            str_contains($p, 'cotizaciones enviadas')
        ) {
            $cantidad = Cotizacion::where(
                'estado',
                'enviada'
            )->count();

            return "Hay {$cantidad} cotizaciones pendientes.";
        }

        /*
         * ==========================================
         * COTIZACIONES APROBADAS
         * ==========================================
         */

        if (
            str_contains($p, 'cotizaciones aprobadas')
        ) {
            $cantidad = Cotizacion::where(
                'estado',
                'aprobada'
            )->count();

            return "Hay {$cantidad} cotizaciones aprobadas.";
        }

        /*
         * ==========================================
         * MOVIMIENTOS DE INVENTARIO
         * ==========================================
         */

        if (
            str_contains($p, 'entradas de inventario') ||
            str_contains($p, 'cuantas entradas') ||
            str_contains($p, 'cuántas entradas')
        ) {
            $cantidad = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_ENTRADA
            )->sum('cantidad');

            return "Se han registrado {$cantidad} unidades " .
                "como entradas de inventario.";
        }

        if (
            str_contains($p, 'salidas de inventario') ||
            str_contains($p, 'cuantas salidas') ||
            str_contains($p, 'cuántas salidas')
        ) {
            $cantidad = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_SALIDA
            )->sum('cantidad');

            return "Se han registrado {$cantidad} unidades " .
                "como salidas de inventario.";
        }

        /*
         * ==========================================
         * No fue una consulta directa.
         * Ollama se encargará.
         * ==========================================
         */

        return null;
    }

    /**
     * Construye el contexto operativo para Ollama.
     */
    private function construirContextoOperativo(): string
    {
        /*
         * ==========================================
         * MATERIALES
         * ==========================================
         */

        $materiales = Material::where('activo', true)
            ->with('proveedor')
            ->orderBy('nombre')
            ->take(20)
            ->get();

        /*
         * ==========================================
         * MATERIALES BAJO STOCK
         * ==========================================
         */

        $bajoStock = Material::where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->orderBy('nombre')
            ->get();

        /*
         * ==========================================
         * PROVEEDORES
         * ==========================================
         */

        $proveedores = Proveedor::where('activo', true)
            ->orderBy('nombre')
            ->get();

        /*
         * ==========================================
         * TICKETS
         * ==========================================
         */

        $ticketsAbiertos = Ticket::whereNotIn(
            'estado',
            ['completado', 'cancelado']
        )->count();

        $ticketsPendientes = Ticket::where(
            'estado',
            'pendiente'
        )->count();

        /*
         * ==========================================
         * COTIZACIONES
         * ==========================================
         */

        $cotizacionesPendientes = Cotizacion::where(
            'estado',
            'enviada'
        )->count();

        /*
         * ==========================================
         * MATERIALES A TEXTO
         * ==========================================
         */

        $materialesTexto = $materiales->map(
            function ($material) {

                $proveedor = $material->proveedor?->nombre
                    ?? 'Sin proveedor';

                $precio = number_format(
                    (float) $material->precio,
                    2
                );

                return "{$material->nombre}: " .
                    "stock {$material->stock}, " .
                    "mínimo {$material->stock_minimo}, " .
                    "precio Q{$precio}, " .
                    "proveedor {$proveedor}";
            }
        )->implode("\n");

        /*
         * ==========================================
         * BAJO STOCK A TEXTO
         * ==========================================
         */

        $bajoStockTexto = $bajoStock->map(
            function ($material) {
                return "{$material->nombre}: " .
                    "stock {$material->stock}, " .
                    "mínimo {$material->stock_minimo}";
            }
        )->implode("\n");

        $bajoStockTextoFinal = $bajoStockTexto !== ''
            ? $bajoStockTexto
            : 'Ninguno';

        /*
         * ==========================================
         * PROVEEDORES A TEXTO
         * ==========================================
         */

        $proveedoresTexto = $proveedores
            ->pluck('nombre')
            ->implode(', ');

        $proveedoresTextoFinal = $proveedoresTexto !== ''
            ? $proveedoresTexto
            : 'Ninguno';

        /*
         * ==========================================
         * CONTEXTO PARA OLLAMA
         * ==========================================
         */

        return <<<CONTEXTO
Eres el asistente inteligente de OptiGest,
un sistema de gestión operativa de una empresa.

Responde únicamente en español.

REGLAS:
- Da únicamente la respuesta final.
- No muestres razonamientos internos.
- No escribas "Thinking", "Okay", "Let me think" ni frases similares.
- Sé breve, claro y útil.
- No inventes información.
- Utiliza únicamente los datos proporcionados.
- Si los datos no permiten responder, dilo claramente.
- Cuando la pregunta sea sobre inventario, materiales,
  proveedores, tickets o cotizaciones, utiliza los datos
  proporcionados por OptiGest.

DATOS ACTUALES DE OPTIGEST

Materiales activos: {$materiales->count()}

MATERIALES BAJO STOCK:
{$bajoStockTextoFinal}

Proveedores activos: {$proveedores->count()}

PROVEEDORES:
{$proveedoresTextoFinal}

Tickets abiertos: {$ticketsAbiertos}

Tickets pendientes: {$ticketsPendientes}

Cotizaciones pendientes: {$cotizacionesPendientes}

MATERIALES REGISTRADOS:
{$materialesTexto}

Si la pregunta no puede responderse con estos datos,
indica brevemente que la información no está disponible.

CONTEXTO;
    }

    /**
     * Consulta Ollama.
     */
    private function consultarOllama(
        string $contexto,
        string $pregunta
    ): string {
        try {
            /*
             * Ollama puede tardar varios segundos en cargar
             * y generar la respuesta de Qwen3:4b.
             *
             * Se aumenta el timeout para evitar que Laravel
             * cancele la solicitud prematuramente.
             */
            $response = Http::connectTimeout(5)
                ->timeout(90)
                ->post(
                    'http://127.0.0.1:11434/api/chat',
                    [
                        'model' => 'qwen3:4b',

                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $contexto,
                            ],
                            [
                                'role' => 'user',
                                'content' => $pregunta,
                            ],
                        ],

                        /*
                         * No utilizar streaming.
                         */
                        'stream' => false,

                        /*
                         * Solicitar a Ollama que no utilice
                         * razonamiento cuando el modelo lo admita.
                         */
                        'think' => false,

                        /*
                         * Mantener el modelo cargado temporalmente
                         * para evitar una carga completa en cada pregunta.
                         */
                        'keep_alive' => '5m',

                        'options' => [
                            'temperature' => 0.1,

                            /*
                             * Limita la cantidad de tokens generados
                             * para obtener respuestas breves.
                             */
                            'num_predict' => 80,
                        ],
                    ]
                );

            /*
             * ==========================================
             * VERIFICAR RESPUESTA HTTP
             * ==========================================
             */

            if ($response->failed()) {
                return 'Ollama respondió con un error HTTP ' .
                    $response->status() . '.';
            }

            /*
             * ==========================================
             * OBTENER MENSAJE
             * ==========================================
             */

            $mensaje = $response->json('message');

            if (!is_array($mensaje)) {
                return 'El asistente IA no devolvió una respuesta válida.';
            }

            $texto = $mensaje['content'] ?? '';

            if (!is_string($texto)) {
                $texto = '';
            }

            $texto = trim($texto);

            /*
             * ==========================================
             * LIMPIAR RAZONAMIENTO DE QWEN
             * ==========================================
             *
             * Dependiendo de la versión de Ollama y del
             * modelo, el razonamiento puede aparecer:
             *
             * <think>...</think>
             *
             * o puede aparecer solamente antes de </think>.
             */

            if (str_contains($texto, '</think>')) {
                $partes = explode(
                    '</think>',
                    $texto
                );

                $texto = trim(
                    end($partes)
                );
            }

            /*
             * Eliminar cualquier bloque <think>...</think>
             * que todavía exista.
             */
            $texto = preg_replace(
                '/<think>.*?<\/think>/is',
                '',
                $texto
            );

            /*
             * Eliminar etiquetas restantes.
             */
            $texto = preg_replace(
                '/<\/?think>/i',
                '',
                $texto
            );

            $texto = trim($texto);

            /*
             * ==========================================
             * RESPUESTA FINAL
             * ==========================================
             */

            if ($texto === '') {
                return 'El asistente IA no generó una respuesta.';
            }

            return $texto;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            return 'No se pudo conectar con Ollama. ' .
                'Verifica que Ollama esté ejecutándose.';

        } catch (\Illuminate\Http\Client\RequestException $e) {

            return 'Ollama rechazó la solicitud. ' .
                'Código HTTP: ' . $e->response->status() . '.';

        } catch (\Throwable $e) {

            /*
             * Durante las pruebas mostramos un mensaje
             * más útil que el anterior mensaje genérico.
             */
            return 'Se produjo un error al consultar el asistente IA: ' .
                $e->getMessage();
        }
    }
}

