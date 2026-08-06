<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('proveedor')->orderBy('nombre');

        if ($busqueda = $request->get('q')) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('categoria', 'like', "%{$busqueda}%");
            });
        }

        if ($request->boolean('bajo_stock')) {
            $query->bajoStock();
        }

        $materiales = $query->paginate(15)->withQueryString();

        return view('inventario.materiales.index', [
            'materiales' => $materiales,
            'totalBajoStock' => Material::bajoStock()->count(),
        ]);
    }

    public function create()
    {
        return view('inventario.materiales.create', [
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validarDatos($request);
        $datos['codigo'] = Material::generarCodigo();

        $material = Material::create($datos);

        return redirect()->route('inventario.materiales.index')
            ->with('status', "Material {$material->codigo} registrado correctamente.");
    }

    public function edit(Material $material)
    {
        return view('inventario.materiales.edit', [
            'material' => $material,
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $datos = $this->validarDatos($request, $material->id);
        unset($datos['stock']);

        $material->update($datos);

        return redirect()->route('inventario.materiales.index')
            ->with('status', "Material {$material->codigo} actualizado correctamente.");
    }

    public function destroy(Material $material)
    {
        $material->update(['activo' => false]);

        return redirect()->route('inventario.materiales.index')
            ->with('status', "Material {$material->codigo} desactivado.");
    }

    private function validarDatos(Request $request, ?int $idIgnorar = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'unidad_medida' => ['required', 'string', 'max:30'],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')],
        ]);
    }
}
