<?php

namespace App\Http\Controllers\IA;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\ConversacionIA;
use App\Models\Material;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsistenteController extends Controller
{
    public function index(Request $request)
    {
        return view('asistente.index', [
            'historial' => ConversacionIA::where('usuario_id', $request->user()->id)
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

        $contexto = $this->construirContextoOperativo();

        $respuesta = $this->consultarOllama(
            $contexto,
            $datos['pregunta']
        );

        $conversacion = ConversacionIA::create([
            'usuario_id' => $request->user()->id,
            'pregunta' => $datos['pregunta'],
            'respuesta' => $respuesta,
        ]);

        return back()->with('ultimaRespuesta', $conversacion);
    }

    private function construirContextoOperativo(): string
    {
        $bajoStock = Material::bajoStock()
            ->where('activo', true)
            ->get(['nombre', 'stock', 'stock_minimo']);

        $ticketsAbiertos = Ticket::whereNotIn(
            'estado',
            ['completado', 'cancelado']
        )->count();

        $cotizacionesPendientes = Cotizacion::where(
            'estado',
            'enviada'
        )->count();

        $resumenStock = $bajoStock
            ->map(fn ($m) =>
                "{$m->nombre} (stock: {$m->stock}, mínimo: {$m->stock_minimo})"
            )
            ->implode('; ');

        return "Eres el asistente gerencial de OptiGest, "
            ."sistema de gestión operativa de Constru Fontanería Cadiliompa.\n\n"
            ."Responde únicamente con la respuesta final, en español, "
            ."de forma clara y breve. No muestres razonamientos internos "
            ."ni explicaciones sobre cómo generaste la respuesta.\n\n"
            ."Datos operativos actuales:\n"
            ."- Tickets de servicio abiertos: {$ticketsAbiertos}\n"
            ."- Cotizaciones pendientes de aprobación: {$cotizacionesPendientes}\n"
            ."- Materiales con bajo stock: "
            .($resumenStock ?: 'ninguno');
    }

    private function consultarOllama(
        string $contexto,
        string $pregunta
    ): string {
        try {
            $response = Http::timeout(120)->post(
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

                    'options' => [
                        'temperature' => 0.2,
                        'num_predict' => 300,
                    ],
                ]
            );

            if ($response->failed()) {
                return 'No fue posible contactar al asistente IA. '
                    .'Verifica que Ollama esté ejecutándose.';
            }

            $texto = $response->json('message.content', '');

            if (!$texto) {
                return 'El asistente no generó una respuesta. '
                    .'Intenta reformular la pregunta.';
            }

            /*
             * Qwen3 puede incluir su razonamiento antes de </think>.
             * OptiGest solamente mostrará la respuesta final.
             */
            if (str_contains($texto, '</think>')) {
                $texto = substr(
                    $texto,
                    strrpos($texto, '</think>') + strlen('</think>')
                );
            }

            $texto = trim($texto);

            return $texto ?: 'El asistente no generó una respuesta válida.';

        } catch (\Throwable $e) {
            return 'Ocurrió un error al consultar el asistente IA: '
                .$e->getMessage();
        }
    }
}
