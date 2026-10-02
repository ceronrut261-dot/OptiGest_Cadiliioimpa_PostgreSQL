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

        // 1. Extraer datos operativos desde PostgreSQL
        $contexto = $this->construirContextoOperativo($pregunta);

        // 2. Consultar el modelo de IA con fallback automático
        $respuesta = $this->consultarGemini(
            $contexto,
            $pregunta
        );

        // 3. Registrar el historial en la base de datos
        $conversacion = ConversacionIA::create([
            'usuario_id' => $request->user()->id,
            'pregunta'   => $pregunta,
            'respuesta'  => $respuesta,
        ]);

        return back()->with(
            'ultimaRespuesta',
            $conversacion
        );
    }

    /**
     * Extrae información de PostgreSQL según la pregunta para alimentar al modelo.
     */
    private function construirContextoOperativo(string $pregunta): string
    {
        $preguntaNormalizada = mb_strtolower($pregunta);

        $totalMateriales = Material::where('activo', true)->count();
        $totalProveedores = Proveedor::where('activo', true)->count();

        // Conteo de tickets según estado
        $ticketsAbiertos = Ticket::whereNotIn('estado', ['completado', 'cancelado'])->count();
        $ticketsResueltos = Ticket::whereIn('estado', ['completado', 'resuelto'])->count();
        $ticketsPendientes = Ticket::where('estado', 'pendiente')->count();
        $ticketsAsignados = Ticket::where('estado', 'asignado')->count();
        $ticketsEnProceso = Ticket::where('estado', 'en_proceso')->count();
        $ticketsPrioritarios = Ticket::whereIn('prioridad', ['alta', 'urgente'])
            ->whereNotIn('estado', ['completado', 'cancelado'])
            ->count();

        $totalCotizaciones = Cotizacion::count();

        $bajoStock = Material::where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('nombre')
            ->get(['nombre', 'stock', 'stock_minimo']);

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
                ->get(['nombre', 'codigo', 'stock', 'stock_minimo', 'precio', 'unidad_medida']);

            $datosMateriales = $materiales
                ->map(function ($material) {
                    return "- {$material->nombre} | código: {$material->codigo} | stock: {$material->stock} | mínimo: {$material->stock_minimo} | precio: Q" . number_format((float) $material->precio, 2) . " | unidad: {$material->unidad_medida}";
                })
                ->implode("\n");
        }

        $datosProveedores = '';
        if (
            str_contains($preguntaNormalizada, 'proveedor') ||
            str_contains($preguntaNormalizada, 'proveedores')
        ) {
            $proveedores = Proveedor::where('activo', true)
                ->orderBy('nombre')
                ->get(['nombre', 'nit', 'contacto', 'telefono', 'email']);

            $datosProveedores = $proveedores
                ->map(function ($proveedor) {
                    return "- {$proveedor->nombre} | contacto: " . ($proveedor->contacto ?? 'N/D') . " | teléfono: " . ($proveedor->telefono ?? 'N/D');
                })
                ->implode("\n");
        }

        $datosTickets = implode("\n", [
            "Tickets abiertos: {$ticketsAbiertos}",
            "Tickets resueltos / completados: {$ticketsResueltos}",
            "Tickets pendientes: {$ticketsPendientes}",
            "Tickets asignados: {$ticketsAsignados}",
            "Tickets en proceso: {$ticketsEnProceso}",
            "Tickets prioritarios: {$ticketsPrioritarios}",
        ]);

        $datosCotizaciones = '';
        if (
            str_contains($preguntaNormalizada, 'cotizacion') ||
            str_contains($preguntaNormalizada, 'cotizaciones') ||
            str_contains($preguntaNormalizada, 'presupuesto')
        ) {
            $borradores = Cotizacion::where('estado', 'borrador')->count();
            $enviadas = Cotizacion::where('estado', 'enviada')->count();
            $aprobadas = Cotizacion::where('estado', 'aprobada')->count();
            $rechazadas = Cotizacion::where('estado', 'rechazada')->count();
            $valorAprobadas = Cotizacion::where('estado', 'aprobada')->sum('total');

            $datosCotizaciones = implode("\n", [
                "Total de cotizaciones: {$totalCotizaciones}",
                "Borradores: {$borradores}",
                "Enviadas: {$enviadas}",
                "Aprobadas: {$aprobadas}",
                "Rechazadas: {$rechazadas}",
                "Valor de aprobadas: Q" . number_format((float) $valorAprobadas, 2),
            ]);
        }

        $datosMovimientos = '';
        if (
            str_contains($preguntaNormalizada, 'movimiento') ||
            str_contains($preguntaNormalizada, 'entrada') ||
            str_contains($preguntaNormalizada, 'salida')
        ) {
            $entradas = MovimientoInventario::where('tipo', MovimientoInventario::TIPO_ENTRADA)->sum('cantidad');
            $salidas = MovimientoInventario::where('tipo', MovimientoInventario::TIPO_SALIDA)->sum('cantidad');

            $datosMovimientos = implode("\n", [
                "Unidades de entrada: {$entradas}",
                "Unidades de salida: {$salidas}",
            ]);
        }

        $detalleBajoStock = $bajoStock->map(function ($material) {
            return "- {$material->nombre}: stock {$material->stock}, mínimo {$material->stock_minimo}";
        })->implode("\n");

        return <<<CONTEXTO
Eres el asistente inteligente de OptiGest (Constru Fontanería Cadiliompa).

REGLAS DE RESPUESTA:
1. Responde siempre en español de forma directa, profesional y concisa.
2. Utiliza los DATOS INTERNOS proporcionados a continuación para responder sobre el negocio.
3. Para montos o precios internos, exprésalos en Quetzales (Q).
4. No menciones "según los datos proporcionados" ni des explicaciones redundantes; entrega la respuesta concreta.

DATOS OPERATIVOS DE OPTIGEST:
- Materiales activos en sistema: {$totalMateriales}
- Proveedores activos: {$totalProveedores}

ESTADO DE TICKETS:
{$datosTickets}

DETALLE BAJO STOCK ({$bajoStock->count()} materiales):
{$detalleBajoStock}

MATERIALES:
{$datosMateriales}

PROVEEDORES:
{$datosProveedores}

COTIZACIONES:
{$datosCotizaciones}

MOVIMIENTOS DE INVENTARIO:
{$datosMovimientos}
CONTEXTO;
    }

    /**
     * Consulta con sistema de fallback automático ante saturaciones temporales (503).
     */
    private function consultarGemini(string $contexto, string $pregunta): string
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return 'No se ha configurado la variable GEMINI_API_KEY en el archivo .env.';
        }

        // Lista de modelos a probar por prioridad en caso de saturación
        $modelos = [
            'gemini-3.8-flash',
            'gemini-2.5-flash',
            'gemini-1.5-flash'
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $contexto]
                ]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $pregunta]
                    ]
                ]
            ]
        ];

        $ultimoError = '';

        foreach ($modelos as $modelo) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(25)->post($url, $payload);

                if ($response->successful()) {
                    $datos = $response->json();
                    $texto = $datos['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($texto) {
                        return trim($texto);
                    }
                }

                $status = $response->status();

                // Si hay saturación (503), modelo no encontrado (404) o error de servidor (500), pasa al siguiente
                if (in_array($status, [503, 404, 500])) {
                    $ultimoError = 'El servicio experimentó alta demanda en este momento. Por favor intenta de nuevo en unos segundos.';
                    continue;
                }

                if ($status === 429) {
                    return 'Límite de consultas por minuto alcanzado. Espera unos segundos y vuelve a preguntar.';
                }

                $ultimoError = 'Error de comunicación: ' . $response->body();

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                continue;
            } catch (\Throwable $e) {
                $ultimoError = $e->getMessage();
            }
        }

        return $ultimoError ?: 'El servicio de IA no está disponible temporalmente. Intenta nuevamente.';
    }
}