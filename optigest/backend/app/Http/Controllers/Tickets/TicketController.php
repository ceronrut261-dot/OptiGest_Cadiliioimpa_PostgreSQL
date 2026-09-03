<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
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
        ]);

        $datos['codigo'] = Ticket::generarCodigo();
        $datos['estado'] = ($datos['tecnico_id'] ?? null) ? 'asignado' : 'pendiente';

        $ticket = Ticket::create($datos);

        return redirect()->route('tickets.show', $ticket)
            ->with('status', "Ticket {$ticket->codigo} creado correctamente.");
    }

    public function show(Ticket $ticket)
    {
        return view('tickets.show', ['ticket' => $ticket->load(['tecnico', 'cliente', 'cotizaciones'])]);
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return view('tickets.create', [
            'ticket' => $ticket,
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
        ]);

        $ticket->update($datos);

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
}
