<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::withCount('materiales')->orderBy('nombre');

        if ($busqueda = $request->get('q')) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        }

        return view('proveedores.index', ['proveedores' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $proveedor = Proveedor::create($this->validarDatos($request));

        return redirect()->route('proveedores.index')
            ->with('status', "Proveedor {$proveedor->nombre} registrado correctamente.");
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.create', ['proveedor' => $proveedor]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validarDatos($request));

        return redirect()->route('proveedores.index')
            ->with('status', "Proveedor {$proveedor->nombre} actualizado correctamente.");
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => false]);

        return redirect()->route('proveedores.index')
            ->with('status', "Proveedor {$proveedor->nombre} desactivado.");
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:20'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
