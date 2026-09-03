<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::withCount(['tickets', 'cotizaciones'])->orderBy('nombre');

        if ($busqueda = $request->get('q')) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%");
            });
        }

        return view('clientes.index', ['clientes' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($this->validarDatos($request));

        // Si el cliente se está creando desde el formulario de un ticket o
        // una cotización (ver botón "Cliente nuevo" en esas vistas), regresa
        // directo a esa pantalla con el cliente recién creado ya disponible.
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
