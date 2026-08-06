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
                ->orderByDesc('created_at')->take(10)->get(),
        ]);
    }

    public function consultar(Request $request)
    {
        $datos = $request->validate(['pregunta' => ['required', 'string', 'max:1000']]);

        $contexto = $this->construirContextoOperativo();
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            $respuesta = 'El asistente IA no esta configurado todavia. '
                .'Pide al administrador que defina ANTHROPIC_API_KEY en el archivo .env del servidor.';
        } else {
            $respuesta = $this->consultarClaude($apiKey, $contexto, $datos['pregunta']);
        }

        $conversacion = ConversacionIA::create([
            'usuario_id' => $request->user()->id,
            'pregunta' => $datos['pregunta'],
            'respuesta' => $respuesta,
        ]);

        return back()->with('ultimaRespuesta', $conversacion);
    }

    private function construirContextoOperativo(): string
    {
        $bajoStock = Material::bajoStock()->where('activo', true)->get(['nombre', 'stock', 'stock_minimo']);
        $ticketsAbiertos = Ticket::whereNotIn('estado', ['completado', 'cancelado'])->count();
        $cotizacionesPendientes = Cotizacion::where('estado', 'enviada')->count();

        $resumenStock = $bajoStock->map(fn ($m) => "{$m->nombre} (stock: {$m->stock}, minimo: {$m->stock_minimo})")->implode('; ');

        return "Eres el asistente gerencial de OptiGest, el sistema de gestion operativa de la empresa "
            ."Constru Fontaneria Cadiliompa. Responde de forma breve y en espanol.\n"
            ."Datos operativos actuales:\n"
            ."- Tickets de servicio abiertos: {$ticketsAbiertos}\n"
            ."- Cotizaciones pendientes de aprobacion: {$cotizacionesPendientes}\n"
            .'- Materiales con bajo stock: '.($resumenStock ?: 'ninguno');
    }

    private function consultarClaude(string $apiKey, string $contexto, string $pregunta): string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
                'max_tokens' => 500,
                'system' => $contexto,
                'messages' => [['role' => 'user', 'content' => $pregunta]],
            ]);

            if ($response->failed()) {
                return 'No fue posible contactar al asistente IA en este momento. Intenta de nuevo mas tarde.';
            }

            $texto = collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode("\n");

            return $texto ?: 'El asistente no genero una respuesta. Intenta reformular tu pregunta.';
        } catch (\Throwable $e) {
            return 'Ocurrio un error al consultar al asistente IA: '.$e->getMessage();
        }
    }
}
