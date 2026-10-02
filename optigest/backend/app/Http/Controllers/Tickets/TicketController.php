<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAsignado;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['tecnico', 'cliente'])->orderByDesc('created_at');

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        return view('tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'estados' => Ticket::ESTADOS,
        ]);
    }

    public function create()
    {
        return view('tickets.create', [
            'tecnicos' => User::role('tecnico')->orderBy('name')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'descripcion' => ['required', 'string'],
            'prioridad' => ['required', 'in:baja,media,alta,urgente'],
            'tecnico_id' => ['nullable', 'exists:users,id'],
            'fecha_programada' => ['nullable', 'date'],
            'monto_trato_principal' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'equipo' => ['nullable', 'array'],
            'equipo.*.user_id' => ['nullable', 'exists:users,id'],
            'equipo.*.monto_trato' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ]);

        unset($datos['monto_trato_principal'], $datos['equipo']);

        $datos['codigo'] = Ticket::generarCodigo();
        $datos['estado'] = ($datos['tecnico_id'] ?? null) ? 'asignado' : 'pendiente';

        $ticket = Ticket::create($datos);

        $nuevos = $this->sincronizarEquipo($request, $ticket);

        User::whereIn('id', $nuevos)->get()
            ->each(fn (User $tecnico) => $tecnico->notify(new TicketAsignado($ticket)));

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Ticket {$ticket->codigo} creado correctamente.");
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['tecnico', 'cliente', 'cotizaciones', 'tecnicos', 'salidas']);
        $ticket->load(['gastos' => fn ($q) => $q->with(['registrador', 'cotizacion'])->orderByDesc('fecha_gasto')->orderByDesc('id')]);

        $veFinanzas = auth()->user()->hasRole(['administrador', 'cotizador']);

        return view('tickets.show', [
            'ticket' => $ticket,
            'veFinanzas' => $veFinanzas,
            'resumen' => $veFinanzas ? $ticket->resumenFinanciero() : null,
            'puedeEditar' => auth()->user()->can('update', $ticket),
        ]);
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return view('tickets.create', [
            'ticket' => $ticket->load('tecnicos'),
            'tecnicos' => User::role('tecnico')->orderBy('name')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $datos = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'descripcion' => ['required', 'string'],
            'prioridad' => ['required', 'in:baja,media,alta,urgente'],
            'tecnico_id' => ['nullable', 'exists:users,id'],
            'fecha_programada' => ['nullable', 'date'],
            'monto_trato_principal' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'equipo' => ['nullable', 'array'],
            'equipo.*.user_id' => ['nullable', 'exists:users,id'],
            'equipo.*.monto_trato' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ]);

        unset($datos['monto_trato_principal'], $datos['equipo']);

        $ticket->update($datos);

        // Notifica solo a quien se suma al equipo (evita reenviar el correo
        // cuando se edita el ticket sin cambiar a las personas asignadas).
        $nuevos = $this->sincronizarEquipo($request, $ticket);

        User::whereIn('id', $nuevos)->get()
            ->each(fn (User $tecnico) => $tecnico->notify(new TicketAsignado($ticket)));

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Ticket {$ticket->codigo} actualizado correctamente.");
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->update(['estado' => 'cancelado']);

        return redirect()->route('tickets.index')->with('status', "Ticket {$ticket->codigo} cancelado.");
    }

    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        $this->authorize('cambiarEstado', $ticket);

        $datos = $request->validate(['estado' => ['required', 'in:pendiente,asignado,en_proceso,completado,cancelado']]);

        $ticket->estado = $datos['estado'];

        if ($datos['estado'] === 'completado') {
            $ticket->fecha_completado = now();
        }

        $ticket->save();

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Estado del ticket {$ticket->codigo} actualizado a '{$datos['estado']}'.");
    }
    /**
     * Mantiene la tabla ticket_tecnicos (responsable + apoyos con su pago
     * por trato). Solo administrador/cotizador pueden tocar los montos y
     * el equipo de apoyo; un técnico que edita su ticket no ve ni cambia
     * el pago de nadie. Devuelve los ids que se sumaron al equipo.
     */
    private function sincronizarEquipo(Request $request, Ticket $ticket): array
    {
        $antes = $ticket->tecnicos()->pluck('users.id')->all();

        if ($request->user()->hasRole(['administrador', 'cotizador'])) {
            $filas = [];

            if ($ticket->tecnico_id) {
                $filas[$ticket->tecnico_id] = [
                    'rol' => 'responsable',
                    'monto_trato' => (float) $request->input('monto_trato_principal', 0),
                ];
            }

            foreach ($request->input('equipo', []) as $miembro) {
                $id = (int) ($miembro['user_id'] ?? 0);

                if ($id === 0 || isset($filas[$id])) {
                    continue;
                }

                $filas[$id] = [
                    'rol' => 'apoyo',
                    'monto_trato' => (float) ($miembro['monto_trato'] ?? 0),
                ];
            }

            $ticket->tecnicos()->sync($filas);
        } elseif ($ticket->tecnico_id) {
            $ticket->tecnicos()->syncWithoutDetaching([
                $ticket->tecnico_id => ['rol' => 'responsable'],
            ]);
        }

        $despues = $ticket->tecnicos()->pluck('users.id')->all();

        return array_values(array_diff($despues, $antes));
    }
}
