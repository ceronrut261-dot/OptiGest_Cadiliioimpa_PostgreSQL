<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use Illuminate\Http\Request;

/**
 * Servicios con precio de mano de obra estandarizado. Solo el administrador
 * los crea o cambia, así el precio no varía de una cotización a otra.
 */
class CatalogoServicioController extends Controller
{
    public function create()
    {
        return view('catalogo.servicio_form');
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['codigo'] = CatalogoServicio::generarCodigo();
        $datos['activo'] = true;

        CatalogoServicio::create($datos);

        return redirect()->route('catalogo.index', ['vista' => 'servicios'])
            ->with('status', 'Servicio agregado al catálogo.');
    }

    public function edit(CatalogoServicio $servicio)
    {
        return view('catalogo.servicio_form', ['servicio' => $servicio]);
    }

    public function update(Request $request, CatalogoServicio $servicio)
    {
        $datos = $this->validar($request);
        $datos['activo'] = $request->boolean('activo');

        $servicio->update($datos);

        return redirect()->route('catalogo.index', ['vista' => 'servicios'])
            ->with('status', "Servicio {$servicio->codigo} actualizado.");
    }

    /**
     * "Eliminar" desactiva: las cotizaciones viejas conservan su historial.
     */
    public function destroy(CatalogoServicio $servicio)
    {
        $servicio->update(['activo' => false]);

        return redirect()->route('catalogo.index', ['vista' => 'servicios'])
            ->with('status', "Servicio {$servicio->codigo} desactivado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'unidad' => ['required', 'string', 'max:30'],
            'precio_estandar' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);
    }
}
