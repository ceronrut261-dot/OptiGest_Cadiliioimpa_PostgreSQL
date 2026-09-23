<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $verInactivos = $request->boolean('inactivos');

        $query = Cliente::withCount(['tickets', 'cotizaciones'])
            ->when(!$verInactivos, fn ($q) => $q->where('activo', true))
            ->orderBy('nombre');

        if ($busqueda = $request->get('q')) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%");
            });
        }

        return view('clientes.index', [
            'clientes' => $query->paginate(15)->withQueryString(),
            'verInactivos' => $verInactivos,
        ]);
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($this->validarDatos($request));

        if ($request->get('origen') === 'ticket') {
            return redirect()->route('tickets.create')
                ->with('status', "Cliente {$cliente->nombre} registrado. Ya puedes seleccionarlo en el ticket.");
        }

        if ($request->get('origen') === 'cotizacion') {
            return redirect()->route('cotizaciones.create')
                ->with('status', "Cliente {$cliente->nombre} registrado. Ya puedes seleccionarlo en la cotización.");
        }

        return redirect()->route('clientes.index')
            ->with('status', "Cliente {$cliente->nombre} registrado correctamente.");
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.create', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($this->validarDatos($request));

        return redirect()->route('clientes.index')
            ->with('status', "Cliente {$cliente->nombre} actualizado correctamente.");
    }

    /**
     * "Eliminar" un cliente en realidad lo desactiva: no aparece en el
     * listado normal, pero su historial de tickets/cotizaciones se
     * conserva intacto (la base de datos no permite borrarlo de verdad
     * si ya tiene tickets o cotizaciones asociadas).
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        return redirect()->route('clientes.index')
            ->with('status', "Cliente {$cliente->nombre} desactivado.");
    }

    public function reactivar(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);

        return redirect()->route('clientes.index', ['inactivos' => 1])
            ->with('status', "Cliente {$cliente->nombre} reactivado.");
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }
}
