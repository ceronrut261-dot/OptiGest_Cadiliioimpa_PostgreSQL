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
         * =====================================================
         * 1. PRIMERO CONSULTAMOS DIRECTAMENTE POSTGRESQL
         * =====================================================
         */
        $respuestaDirecta = $this->consultarBaseDatos($pregunta);

        if ($respuestaDirecta !== null) {
            $respuesta = $respuestaDirecta;
        } else {
            /*
             * =================================================
             * 2. SOLO SI NO ES UNA CONSULTA DIRECTA,
             *    UTILIZAMOS OLLAMA
             * =================================================
             */
            $contexto = $this->construirContextoOperativo();

            $respuesta = $this->consultarOllama(
                $contexto,
                $pregunta
            );
        }

        /*
         * =====================================================
         * 3. GUARDAR CONVERSACIÓN
         * =====================================================
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
     * =========================================================
     * CONSULTAR DIRECTAMENTE LA BASE DE DATOS
     * =========================================================
     */
    private function consultarBaseDatos(string $pregunta): ?string
    {
        $p = mb_strtolower(trim($pregunta));

        /*
         * Normalizar acentos para facilitar las búsquedas.
         */
        $pSinAcentos = strtr($p, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
        ]);

        /*
         * =====================================================
         * PROVEEDORES - CANTIDAD
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'cuantos proveedores') ||
            str_contains($pSinAcentos, 'numero de proveedores') ||
            str_contains($pSinAcentos, 'cantidad de proveedores')
        ) {
            $cantidad = Proveedor::where('activo', true)->count();

            return "Tienes {$cantidad} proveedores activos.";
        }

        /*
         * =====================================================
         * PROVEEDORES - LISTA
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'que proveedores tengo') ||
            str_contains($pSinAcentos, 'proveedores tengo') ||
            str_contains($pSinAcentos, 'proveedores registrados') ||
            str_contains($pSinAcentos, 'proveedores disponibles') ||
            str_contains($pSinAcentos, 'lista de proveedores')
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
         * =====================================================
         * MATERIALES - CANTIDAD
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'cuantos materiales') ||
            str_contains($pSinAcentos, 'numero de materiales') ||
            str_contains($pSinAcentos, 'cantidad de materiales')
        ) {
            $cantidad = Material::where('activo', true)->count();

            return "Tienes {$cantidad} materiales activos.";
        }

        /*
         * =====================================================
         * MATERIALES BAJO STOCK
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'bajo stock') ||
            str_contains($pSinAcentos, 'bajo de stock') ||
            str_contains($pSinAcentos, 'stock minimo') ||
            str_contains($pSinAcentos, 'necesitan atencion') ||
            str_contains($pSinAcentos, 'deberia revisar') ||
            str_contains($pSinAcentos, 'por agotarse') ||
            str_contains($pSinAcentos, 'reponer') ||
            str_contains($pSinAcentos, 'materiales en riesgo') ||
            str_contains($pSinAcentos, 'materiales con riesgo')
        ) {
            return $this->respuestaMaterialesBajoStock();
        }

        /*
         * =====================================================
         * ESTADO GENERAL DEL INVENTARIO
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'inventario esta bien') ||
            str_contains($pSinAcentos, 'inventario esta completo') ||
            str_contains($pSinAcentos, 'como esta el inventario') ||
            str_contains($pSinAcentos, 'estado del inventario') ||
            str_contains($pSinAcentos, 'estado general del inventario') ||
            str_contains($pSinAcentos, 'inventario')
        ) {
            $totalMateriales = Material::where(
                'activo',
                true
            )->count();

            $bajoStock = Material::where(
                'activo',
                true
            )
                ->whereColumn(
                    'stock',
                    '<=',
                    'stock_minimo'
                )
                ->count();

            if ($bajoStock === 0) {
                return "El inventario se encuentra bien. " .
                    "Hay {$totalMateriales} materiales activos " .
                    "y ninguno está en o por debajo del stock mínimo.";
            }

            return "El inventario requiere atención. " .
                "Hay {$bajoStock} materiales de {$totalMateriales} " .
                "en o por debajo del stock mínimo.";
        }

        /*
         * =====================================================
         * TICKETS PENDIENTES
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'tickets pendientes') ||
            str_contains($pSinAcentos, 'ticket pendiente')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'pendiente'
            )->count();

            return "Hay {$cantidad} tickets pendientes.";
        }

        /*
         * =====================================================
         * TICKETS ABIERTOS
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'tickets abiertos') ||
            str_contains($pSinAcentos, 'tickets estan abiertos')
        ) {
            $cantidad = Ticket::whereNotIn(
                'estado',
                ['completado', 'cancelado']
            )->count();

            return "Hay {$cantidad} tickets abiertos.";
        }

        /*
         * =====================================================
         * TICKETS EN PROCESO
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'tickets en proceso') ||
            str_contains($pSinAcentos, 'tickets estan en proceso')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'en_proceso'
            )->count();

            return "Hay {$cantidad} tickets en proceso.";
        }

        /*
         * =====================================================
         * TICKETS ASIGNADOS
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'tickets asignados') ||
            str_contains($pSinAcentos, 'tickets estan asignados')
        ) {
            $cantidad = Ticket::where(
                'estado',
                'asignado'
            )->count();

            return "Hay {$cantidad} tickets asignados.";
        }

        /*
         * =====================================================
         * COTIZACIONES - TOTAL
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'cuantas cotizaciones') ||
            str_contains($pSinAcentos, 'numero de cotizaciones') ||
            str_contains($pSinAcentos, 'cantidad de cotizaciones')
        ) {
            $cantidad = Cotizacion::count();

            return "Tienes {$cantidad} cotizaciones registradas.";
        }

        /*
         * =====================================================
         * COTIZACIONES PENDIENTES
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'cotizaciones pendientes') ||
            str_contains($pSinAcentos, 'cotizaciones enviadas')
        ) {
            $cantidad = Cotizacion::where(
                'estado',
                'enviada'
            )->count();

            return "Hay {$cantidad} cotizaciones pendientes.";
        }

        /*
         * =====================================================
         * COTIZACIONES APROBADAS
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'cotizaciones aprobadas')
        ) {
            $cantidad = Cotizacion::where(
                'estado',
                'aprobada'
            )->count();

            return "Hay {$cantidad} cotizaciones aprobadas.";
        }

        /*
         * =====================================================
         * ENTRADAS DE INVENTARIO
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'entradas de inventario') ||
            str_contains($pSinAcentos, 'cuantas entradas')
        ) {
            $cantidad = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_ENTRADA
            )->sum('cantidad');

            return "Se han registrado {$cantidad} unidades " .
                "como entradas de inventario.";
        }

        /*
         * =====================================================
         * SALIDAS DE INVENTARIO
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'salidas de inventario') ||
            str_contains($pSinAcentos, 'cuantas salidas')
        ) {
            $cantidad = MovimientoInventario::where(
                'tipo',
                MovimientoInventario::TIPO_SALIDA
            )->sum('cantidad');

            return "Se han registrado {$cantidad} unidades " .
                "como salidas de inventario.";
        }

        /*
         * =====================================================
         * MATERIAL CON MAYOR RIESGO
         * =====================================================
         */
        if (
            str_contains($pSinAcentos, 'mayor riesgo') ||
            str_contains($pSinAcentos, 'mayor peligro') ||
            str_contains($pSinAcentos, 'material mas urgente') ||
            str_contains($pSinAcentos, 'material mas critico') ||
            str_contains($pSinAcentos, 'material que requiere mayor atencion')
        ) {
            $material = Material::where('activo', true)
                ->whereColumn(
                    'stock',
                    '<=',
                    'stock_minimo'
                )
                ->orderByRaw(
                    '(stock - stock_minimo) ASC'
                )
                ->first();

            if (!$material) {
                return 'No hay materiales en riesgo por falta de stock.';
            }

            $faltante = max(
                0,
                $material->stock_minimo - $material->stock
            );

            return "El material que requiere mayor atención es " .
                "{$material->nombre}, porque tiene {$material->stock} " .
                "unidades y su stock mínimo es {$material->stock_minimo}. " .
                "Le faltan {$faltante} unidades para alcanzar el mínimo.";
        }

        /*
         * =====================================================
         * BUSCAR MATERIAL POR PREGUNTA
         *
         * Esto permite:
         *
         * ¿Hay CPVC?
         * ¿Hay pegamento?
         * ¿Hay bombas?
         * ¿Hay bomba de agua?
         * ¿Cuánto pegamento hay?
         * ¿Cuánto CPVC tengo?
         * =====================================================
         */

        $respuestaMaterial = $this->buscarMaterialPorPregunta(
            $pSinAcentos
        );

        if ($respuestaMaterial !== null) {
            return $respuestaMaterial;
        }

        /*
         * =====================================================
         * Si no se pudo resolver directamente, Ollama
         * podrá intentar interpretar la pregunta.
         * =====================================================
         */

        return null;
    }

    /**
     * Buscar materiales directamente en PostgreSQL.
     */
    private function buscarMaterialPorPregunta(
        string $pregunta
    ): ?string {

        /*
         * Palabras que normalmente indican que el usuario
         * está preguntando por existencia o cantidad.
         */
        $indicadores = [
            'hay ',
            'existe ',
            'existen ',
            'tengo ',
            'tenemos ',
            'cuanto ',
            'cuanta ',
            'cuantos ',
            'cuantas ',
            'cantidad de ',
            'disponible ',
            'disponibles ',
        ];

        $esConsultaMaterial = false;

        foreach ($indicadores as $indicador) {
            if (str_contains($pregunta, $indicador)) {
                $esConsultaMaterial = true;
                break;
            }
        }

        if (!$esConsultaMaterial) {
            return null;
        }

        /*
         * =====================================================
         * PALABRAS QUE NO DEBEMOS UTILIZAR COMO NOMBRE
         * =====================================================
         */
        $palabrasIgnorar = [
            'hay',
            'existe',
            'existen',
            'tengo',
            'tenemos',
            'cuanto',
            'cuanta',
            'cuantos',
            'cuantas',
            'cantidad',
            'de',
            'del',
            'la',
            'el',
            'los',
            'las',
            'un',
            'una',
            'unos',
            'unas',
            'material',
            'materiales',
            'producto',
            'productos',
            'disponible',
            'disponibles',
            'stock',
            'tengo',
            'tenemos',
            'en',
            'mi',
            'inventario',
            'por',
            'favor',
            'es',
            'esta',
            'está',
            'registrado',
            'registrados',
            'registrada',
            'registradas',
        ];

        /*
         * Eliminar signos de puntuación.
         */
        $texto = preg_replace(
            '/[¿?¡!,.;:]/u',
            ' ',
            $pregunta
        );

        /*
         * Separar palabras.
         */
        $palabras = preg_split(
            '/\s+/u',
            trim($texto)
        );

        /*
         * Quitar palabras irrelevantes.
         */
        $palabras = array_values(
            array_filter(
                $palabras,
                function ($palabra) use ($palabrasIgnorar) {
                    return !in_array(
                        $palabra,
                        $palabrasIgnorar,
                        true
                    );
                }
            )
        );

        if (empty($palabras)) {
            return null;
        }

        /*
         * Crear posibles términos de búsqueda.
         */
        $terminos = implode(' ', $palabras);

        /*
         * =====================================================
         * BÚSQUEDA FLEXIBLE EN NOMBRE
         * =====================================================
         *
         * PostgreSQL realizará la búsqueda realmente sobre
         * la tabla materiales.
         */
        $materiales = Material::where(
            'activo',
            true
        )
            ->where(function ($query) use ($palabras, $terminos) {

                /*
                 * Primero intentar con la frase completa.
                 */
                $query->where(
                    'nombre',
                    'ILIKE',
                    '%' . $terminos . '%'
                );

                /*
                 * Después buscar cada palabra.
                 */
                foreach ($palabras as $palabra) {
                    if (mb_strlen($palabra) >= 3) {
                        $query->orWhere(
                            'nombre',
                            'ILIKE',
                            '%' . $palabra . '%'
                        );
                    }
                }
            })
            ->orderBy('nombre')
            ->get();

        if ($materiales->isEmpty()) {
            return null;
        }

        /*
         * =====================================================
         * UNA SOLA COINCIDENCIA
         * =====================================================
         */
        if ($materiales->count() === 1) {

            $material = $materiales->first();

            $faltante = max(
                0,
                $material->stock_minimo - $material->stock
            );

            /*
             * Si preguntaron si existe.
             */
            if (
                str_contains($pregunta, 'hay ') ||
                str_contains($pregunta, 'existe ') ||
                str_contains($pregunta, 'existen ')
            ) {
                if ($material->stock > 0) {
                    return "Sí. {$material->nombre} está registrado " .
                        "en OptiGest y actualmente tiene " .
                        "{$material->stock} unidades disponibles.";
                }

                return "Sí. {$material->nombre} está registrado " .
                    "en OptiGest, pero actualmente tiene " .
                    "0 unidades disponibles.";
            }

            /*
             * Si preguntaron cuánto hay.
             */
            return "{$material->nombre} tiene actualmente " .
                "{$material->stock} unidades en inventario. " .
                "Su stock mínimo es de {$material->stock_minimo}.";

        }

        /*
         * =====================================================
         * VARIAS COINCIDENCIAS
         * =====================================================
         */

        $lista = $materiales->map(
            function ($material) {
                return "{$material->nombre} " .
                    "(stock: {$material->stock}, " .
                    "mínimo: {$material->stock_minimo})";
            }
        )->implode(', ');

        return "Encontré varios materiales relacionados: {$lista}.";
    }

    /**
     * =========================================================
     * RESPUESTA DE MATERIALES BAJO STOCK
     * =========================================================
     */
    private function respuestaMaterialesBajoStock(): string
    {
        $materiales = Material::where(
            'activo',
            true
        )
            ->whereColumn(
                'stock',
                '<=',
                'stock_minimo'
            )
            ->orderBy('stock')
            ->orderBy('nombre')
            ->get();

        if ($materiales->isEmpty()) {
            return 'No hay materiales bajo el stock mínimo. El inventario se encuentra bien.';
        }

        $cantidad = $materiales->count();

        $lista = $materiales->map(
            function ($material) {

                $faltante = max(
                    0,
                    $material->stock_minimo - $material->stock
                );

                return "{$material->nombre}: " .
                    "stock {$material->stock}, " .
                    "mínimo {$material->stock_minimo}, " .
                    "faltan {$faltante}";
            }
        )->implode('; ');

        return "Hay {$cantidad} materiales que necesitan atención: {$lista}.";
    }

    /**
     * =========================================================
     * CONSTRUIR CONTEXTO OPERATIVO
     * =========================================================
     */
    private function construirContextoOperativo(): string
    {
        $materiales = Material::where(
            'activo',
            true
        )
            ->with('proveedor')
            ->orderBy('nombre')
            ->get();

        $bajoStock = Material::where(
            'activo',
            true
        )
            ->whereColumn(
                'stock',
                '<=',
                'stock_minimo'
            )
            ->orderBy('stock')
            ->get();

        $proveedores = Proveedor::where(
            'activo',
            true
        )
            ->orderBy('nombre')
            ->get();

        $ticketsAbiertos = Ticket::whereNotIn(
            'estado',
            ['completado', 'cancelado']
        )->count();

        $ticketsPendientes = Ticket::where(
            'estado',
            'pendiente'
        )->count();

        $cotizacionesPendientes = Cotizacion::where(
            'estado',
            'enviada'
        )->count();

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

        $bajoStockTexto = $bajoStock->map(
            function ($material) {
                return "{$material->nombre}: " .
                    "stock {$material->stock}, " .
                    "mínimo {$material->stock_minimo}";
            }
        )->implode("\n");

        if ($bajoStockTexto === '') {
            $bajoStockTexto = 'Ninguno';
        }

        $proveedoresTexto = $proveedores
            ->pluck('nombre')
            ->implode(', ');

        if ($proveedoresTexto === '') {
            $proveedoresTexto = 'Ninguno';
        }

        return <<<CONTEXTO
Eres el asistente inteligente de OptiGest.

Responde únicamente en español.

REGLAS:
- Responde solamente la respuesta final.
- No muestres razonamientos.
- No muestres procesos internos.
- No escribas "Okay", "Let's see", "First", "Thinking" ni frases similares.
- Sé breve y claro.
- No inventes información.
- Utiliza únicamente los datos proporcionados.
- Si no tienes información suficiente, dilo claramente.

DATOS ACTUALES DE OPTIGEST

Materiales activos: {$materiales->count()}

MATERIALES BAJO STOCK:
{$bajoStockTexto}

Proveedores activos: {$proveedores->count()}

PROVEEDORES:
{$proveedoresTexto}

Tickets abiertos: {$ticketsAbiertos}

Tickets pendientes: {$ticketsPendientes}

Cotizaciones pendientes: {$cotizacionesPendientes}

MATERIALES REGISTRADOS:
{$materialesTexto}

CONTEXTO;
    }

    /**
     * =========================================================
     * CONSULTAR OLLAMA
     * =========================================================
     */
    private function consultarOllama(
        string $contexto,
        string $pregunta
    ): string {
        try {

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

                        'stream' => false,

                        'think' => false,

                        'keep_alive' => '5m',

                        'options' => [
                            'temperature' => 0,
                            'num_predict' => 80,
                        ],
                    ]
                );

            if ($response->failed()) {
                return "No fue posible consultar Ollama. " .
                    "Código HTTP: {$response->status()}.";
            }

            $texto = $response->json(
                'message.content'
            );

            if (!is_string($texto)) {
                return 'Ollama no devolvió una respuesta válida.';
            }

            $texto = trim($texto);

            /*
             * Eliminar razonamiento <think>.
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

            $texto = preg_replace(
                '/<think>.*?<\/think>/is',
                '',
                $texto
            );

            $texto = preg_replace(
                '/<\/?think>/i',
                '',
                $texto
            );

            $texto = trim($texto);

            if ($texto === '') {
                return 'Ollama no generó una respuesta.';
            }

            return $texto;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            return 'No se pudo conectar con Ollama. ' .
                'Verifica que Ollama esté ejecutándose.';

        } catch (\Throwable $e) {

            return 'Ocurrió un error al consultar Ollama: ' .
                $e->getMessage();
        }
    }
}