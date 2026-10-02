<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\SalidaMaterial;
use App\Models\Ticket;
use App\Models\TicketGasto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketGastoController extends Controller
{
    private const REGLAS_ARCHIVO = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'];

    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $datos = $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
            'comercio' => ['nullable', 'string', 'max:255'],
            'tipo_documento' => ['required', 'in:'.implode(',', array_keys(TicketGasto::TIPOS_DOCUMENTO))],
            'numero_documento' => ['nullable', 'string', 'max:60'],
            'monto' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'fecha_gasto' => ['required', 'date', 'before_or_equal:today'],
            'cobrar_al_cliente' => ['nullable', 'boolean'],
            'salida_id' => ['nullable', 'exists:salidas_materiales,id'],
            'archivo' => self::REGLAS_ARCHIVO,
            'foto' => self::REGLAS_ARCHIVO,
        ]);

        // La salida debe pertenecer a este mismo ticket.
        if (! empty($datos['salida_id'])
            && ! SalidaMaterial::where('id', $datos['salida_id'])->where('ticket_id', $ticket->id)->exists()) {
            $datos['salida_id'] = null;
        }

        $archivo = $request->file('foto') ?? $request->file('archivo');

        $gasto = new TicketGasto([
            'ticket_id' => $ticket->id,
            'salida_id' => $datos['salida_id'] ?? null,
            'descripcion' => $datos['descripcion'],
            'comercio' => $datos['comercio'] ?? null,
            'tipo_documento' => $archivo || $datos['tipo_documento'] !== 'sin_comprobante'
                ? $datos['tipo_documento'] : 'sin_comprobante',
            'numero_documento' => $datos['numero_documento'] ?? null,
            'monto' => $datos['monto'],
            'fecha_gasto' => $datos['fecha_gasto'],
            'cobrar_al_cliente' => $request->boolean('cobrar_al_cliente'),
            'registrado_por' => $request->user()->id,
        ]);

        if ($archivo) {
            $this->guardarArchivo($gasto, $ticket, $archivo);
        }

        $gasto->save();

        // El gasto cobrable se suma solo a las cotizaciones abiertas.
        $ticket->sincronizarCotizacionesAbiertas();

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Gasto registrado'.($gasto->cobrar_al_cliente ? ' y sumado al cobro del cliente.' : '.'));
    }

    /**
     * Adjuntar (o reemplazar) el comprobante de un gasto ya registrado.
     */
    public function adjuntar(Request $request, Ticket $ticket, TicketGasto $gasto)
    {
        $this->authorize('update', $ticket);
        abort_unless($gasto->ticket_id === $ticket->id, 404);

        $request->validate(['archivo' => self::REGLAS_ARCHIVO, 'foto' => self::REGLAS_ARCHIVO]);

        $archivo = $request->file('foto') ?? $request->file('archivo');

        if (! $archivo) {
            return back()->withErrors(['archivo' => 'Selecciona una foto o un archivo.']);
        }

        $this->borrarArchivo($gasto);
        $this->guardarArchivo($gasto, $ticket, $archivo);

        if ($gasto->tipo_documento === 'sin_comprobante') {
            $gasto->tipo_documento = 'factura';
        }

        $gasto->save();

        return back()->with('status', 'Comprobante adjuntado.');
    }

    /**
     * Los comprobantes se guardan en disco privado: solo se ven con sesión.
     */
    public function archivo(Ticket $ticket, TicketGasto $gasto)
    {
        abort_unless($gasto->ticket_id === $ticket->id && $gasto->archivo_path, 404);
        abort_unless(Storage::disk('local')->exists($gasto->archivo_path), 404);

        return Storage::disk('local')->response(
            $gasto->archivo_path,
            $gasto->archivo_nombre,
            ['Content-Type' => $gasto->archivo_mime ?: 'application/octet-stream']
        );
    }

    /**
     * Reporte PDF de los gastos del ticket con los comprobantes (fotos)
     * incrustados. Los PDF adjuntos no se pueden incrustar: el reporte
     * los menciona y se descargan desde el ticket.
     */
    public function reporte(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['cliente', 'tecnicos']);

        $gastos = $ticket->gastos()->with(['registrador', 'cotizacion'])
            ->orderBy('fecha_gasto')->orderBy('id')->get();

        // Solo jpg/png/gif: dompdf no dibuja webp.
        $imagenes = [];
        foreach ($gastos as $gasto) {
            if (! $gasto->archivo_path
                || ! in_array($gasto->archivo_mime, ['image/jpeg', 'image/png', 'image/gif'], true)
                || ! Storage::disk('local')->exists($gasto->archivo_path)) {
                continue;
            }

            $imagenes[$gasto->id] = 'data:'.$gasto->archivo_mime.';base64,'
                .base64_encode(Storage::disk('local')->get($gasto->archivo_path));
        }

        $pdf = Pdf::loadView('tickets.gastos_pdf', [
            'ticket' => $ticket,
            'gastos' => $gastos,
            'imagenes' => $imagenes,
            'tiposDoc' => TicketGasto::TIPOS_DOCUMENTO,
        ]);

        return $pdf->download('gastos-'.$ticket->codigo.'.pdf');
    }

    public function destroy(Ticket $ticket, TicketGasto $gasto)
    {
        abort_unless($gasto->ticket_id === $ticket->id, 404);

        $cotizacion = $gasto->cotizacion;

        if ($cotizacion && $cotizacion->estado === 'aprobada') {
            return back()->withErrors([
                'gasto' => 'Este gasto ya está cobrado en la cotización aprobada '.$cotizacion->codigo.'; no se puede eliminar.',
            ]);
        }

        $this->borrarArchivo($gasto);
        $gasto->delete();

        $ticket->sincronizarCotizacionesAbiertas();

        return back()->with('status', 'Gasto eliminado.');
    }

    private function guardarArchivo(TicketGasto $gasto, Ticket $ticket, $archivo): void
    {
        $gasto->archivo_path = $archivo->store("comprobantes/{$ticket->id}", 'local');
        $gasto->archivo_nombre = $archivo->getClientOriginalName();
        $gasto->archivo_mime = $archivo->getMimeType();
    }

    private function borrarArchivo(TicketGasto $gasto): void
    {
        if ($gasto->archivo_path) {
            Storage::disk('local')->delete($gasto->archivo_path);
        }
    }
}
