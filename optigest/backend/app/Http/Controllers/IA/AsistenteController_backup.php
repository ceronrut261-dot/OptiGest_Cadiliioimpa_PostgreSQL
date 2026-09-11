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

    public function consultar(Request $request)
    {
        $datos = $request->validate([
            'pregunta' => ['required', 'string', 'max:1000'],
        ]);

        $pregunta = trim($datos['pregunta']);

        /*
         * Construimos solamente los datos necesarios
         * de la base de datos.
         */
        $contexto = $this->construirContextoOperativo($pregunta);

        /*
         * Consultamos Ollama.
         */
        $respuesta = $this->consultarOllama(
            $contexto,
            $pregunta
        );

        /*
         * Guardamos la conversación.
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
     * Construye únicamente el contexto necesario
     * para responder la pregunta.
     */
    private function construirContextoOperativo(string $pregunta): string
    {
        $preguntaNormalizada = mb_strtolower($pregunta);

        /*
         * =====================================================
         * INFORMACIÓN GENERAL
         * =====================================================
         */

        $totalMateriales = Material::where('activo', true)->count();

        $totalProveedores = Proveedor::where('activo', true)->count();

        $ticketsAbiertos = Ticket::whereNotIn(
            'estado',
            ['completado', 'cancelado']
        )->count();

        $totalCotizaciones = Cotizacion::count();

        /*
         * =====================================================
         * INVENTARIO
         * =====================================================
         */

        $bajoStock = Material::where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('nombre')
            ->get([
                'nombre',
                'stock',
                'stock_minimo',
            ]);

        /*
         * Si preguntan específicamente por materiales,
         * enviamos los materiales.
         */
        $datosMateriales = '';

        if (
            str_contains($preguntaNormalizada, 'material') ||
            str_contains($preguntaNormalizada, 'inventario') ||
            str_contains($preguntaNormalizada, 'stock') ||
            str_contains($preguntaNormalizada, 'bomba') ||
            str_contains($preguntaNormalizada, 'producto')
        ) {
            $materiales = Material::where('activo', true)
                ->orderBy('nombre')
                ->get([
                    'nombre',
                    'codigo',
                    'stock',
                    'stock_minimo',
                    'precio',
                    'unidad_medida',
                ]);

            $datosMateriales = $materiales
                ->map(function ($material) {
                    return "- {$material->nombre} | "
                        ."código: {$material->codigo} | "
                        ."stock: {$material->stock} | "
                        ."mínimo: {$material->stock_minimo} | "
                        ."precio: Q".number_format(
                            (float) $material->precio,
                            2
                        )
                        ." | unidad: {$material->unidad_medida}";
                })
                ->implode("\n");
        }

        /*
         * =====================================================
         * PROVEEDORES
         * =====================================================
         */

        $datosProveedores = '';

        if (
            str_contains($preguntaNormalizada, 'proveedor') ||
            str_contains($preguntaNormalizada, 'proveedores')
        ) {
            $proveedores = Proveedor::where('activo', true)
                ->orderBy('nombre')
                ->get([
                    'nombre',
                    'nit',
                    'contacto',
                    'telefono',
                    'email',
                ]);

            $datosProveedores = $proveedores
                ->map(function ($proveedor) {
                    return "- {$proveedor->nombre}"
                        ." | contacto: ".($proveedor->contacto ?? 'N/D')
                        ." | teléfono: ".($proveedor->telefono ?? 'N/D');
                })
                ->implode("\n");
        }

        /*
         * =====================================================
         * TICKETS
         * =====================================================
         */

        $datosTickets = '';

        if (
            str_contains($preguntaNormalizada, 'ticket') ||
            str_contains($preguntaNormalizada, 'servicio')
        ) {
            $pendientes = Ticket::where(
                'estado',
                'pendiente'
            )->count();

            $asignados = Ticket::where(
                'estado',
                'asignado'
            )->count();

            $enProceso = Ticket::where(
                'estado',
                'en_proceso'
            )->count();

            $prioritarios = Ticket::whereIn(
                'prioridad',
                ['alta', 'urgente']
            )
                ->whereNotIn(
                    'estado',
                    ['completado', 'cancelado']
                )
                ->count();

            $datosTickets = implode("\n", [
                "Tickets abiertos: {$ticketsAbiertos}",
                "Tickets pendientes: {$pendientes}",
                "Tickets asignados: {$asignados}",
                "Tickets en proceso: {$enProceso}",
                "Tickets prioritarios: {$prioritarios}",
            ]);
        }

        /*
         * =====================================================
         * COTIZACIONES
         * =====================================================
         */

        $datosCotizaciones = '';

        if (
            str_contains($preguntaNormalizada, 'cotizacion') ||
            str_contains($preguntaNormalizada, 'cotizaciones') ||
            str_contains($preguntaNormalizada, 'presupuesto')
        ) {
            $borradores = Cotizacion::where(
                'estado',
                'borrador'
            )->count();

            $enviadas = Cotizacion::where(
                'estado',
                'enviada'
            )->count();

            $aprobadas = Cotizacion::where(
                'estado',
                'aprobada'
            )->count();

            $rechazadas = Cotizacion::where(
                'estado',
                'rechazada'
            )->count();

            $valorAprobadas = Cotizacion::where(
                'estado',
                'aprobada'
            )->sum('total');

            $datosCotizaciones = implode("\n", [
                "Total de cotizaciones: {$totalCotizaciones}",
                "Borradores: {$borradores}",
                "Enviadas: {$enviadas}",
                "Aprobadas: {$aprobadas}",
                "Rechazadas: {$rechazadas}",
                "Valor de aprobadas: Q"
                    .number_format(
                        (float) $valorAprobadas,
                        2
                    ),
            ]);
        }

        /*
         * =====================================================
         * MOVIMIENTOS DE INVENTARIO
         * =====================================================
         */

        $datosMovimientos = '';

        if (
            str_contains($preguntaNormalizada, 'movimiento') ||
            str_contains($preguntaNormalizada, 'entrada') ||
            str_contains($preguntaNormalizada, 'salida')
        ) {
            $entradas = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_ENTRADA
            )->sum('cantidad');

            $salidas = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_SALIDA
            )->sum('cantidad');

            $datosMovimientos = implode("\n", [
                "Unidades de entrada: {$entradas}",
                "Unidades de salida: {$salidas}",
            ]);
        }

        /*
         * =====================================================
         * CONTEXTO PARA OLLAMA
         * =====================================================
         */

        return <<<CONTEXTO
Eres el asistente IA de OptiGest.

OptiGest es un sistema de gestión operativa de Constru Fontanería Cadiliompa.

REGLAS OBLIGATORIAS:

1. Responde únicamente en español.
2. Responde de forma MUY BREVE.
3. Usa solamente los datos proporcionados.
4. No inventes información.
5. No muestres razonamientos.
6. No muestres análisis.
7. No escribas frases como "el usuario pregunta".
8. No expliques cómo obtuviste la respuesta.
9. Responde directamente la pregunta.
10. Para preguntas de cantidades responde con el número y una breve descripción.
11. Si preguntan por un material específico, utiliza los datos del material.
12. Si no existe información suficiente, responde:
"No tengo esa información disponible en OptiGest."

EJEMPLOS:

Pregunta:
¿Cuántos proveedores tengo?

Respuesta:
Tienes {$totalProveedores} proveedores activos.

Pregunta:
¿Cuántos materiales tengo?

Respuesta:
Tienes {$totalMateriales} materiales activos.

Pregunta:
¿Cuántos tickets están abiertos?

Respuesta:
Hay {$ticketsAbiertos} tickets abiertos.

DATOS GENERALES:

Materiales activos: {$totalMateriales}

Proveedores activos: {$totalProveedores}

Tickets abiertos: {$ticketsAbiertos}

===== MATERIALES =====
{$datosMateriales}

Materiales bajo stock: {$bajoStock->count()}

Detalle bajo stock:
{$bajoStock->map(function ($material) {
    return "- {$material->nombre}: stock {$material->stock}, mínimo {$material->stock_minimo}";
})->implode("\n")}

===== PROVEEDORES =====
{$datosProveedores}

===== TICKETS =====
{$datosTickets}

===== COTIZACIONES =====
{$datosCotizaciones}

===== MOVIMIENTOS =====
{$datosMovimientos}

FIN DE DATOS.
CONTEXTO;
    }

    /**
     * Consulta el modelo Qwen3 mediante Ollama.
     */
    private function consultarOllama(
        string $contexto,
        string $pregunta
    ): string {
        try {
            $response = Http::timeout(60)
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

                        'stream' => false,

                        /*
                         * Evita que Qwen intente mostrar
                         * razonamientos en la respuesta.
                         */
                        'think' => false,

                        'options' => [
                            'temperature' => 0.1,
                            'num_predict' => 50,
                        ],
                    ]
                );

            if ($response->failed()) {
                return 'No fue posible consultar el asistente IA.';
            }

            $texto = $response->json(
                'message.content'
            );

            if (!$texto) {
                return 'El asistente no generó una respuesta.';
            }

            /*
             * Eliminar bloques de pensamiento.
             */
            $texto = preg_replace(
                '/<think>.*?<\/think>/is',
                '',
                $texto
            );

            /*
             * Quitar posibles etiquetas de cierre.
             */
            $texto = str_replace(
                ['</think>', '<think>'],
                '',
                $texto
            );

            /*
             * Si Qwen devuelve su razonamiento sin etiquetas,
             * intentamos quedarnos con la parte final.
             */
            if (
                str_contains($texto, 'Okay,')
                || str_contains($texto, 'The user')
                || str_contains($texto, 'Let me')
                || str_contains($texto, 'First,')
            ) {
                $lineas = preg_split(
                    '/\r\n|\r|\n/',
                    trim($texto)
                );

                $lineas = array_filter(
                    $lineas,
                    function ($linea) {
                        return trim($linea) !== '';
                    }
                );

                $ultimaLinea = end($lineas);

                if ($ultimaLinea) {
                    $texto = $ultimaLinea;
                }
            }

            return trim($texto);

        } catch (\Throwable $e) {
            return 'No fue posible conectar con Ollama.';
        }
    }
}