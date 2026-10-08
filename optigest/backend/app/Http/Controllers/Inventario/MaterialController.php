<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\PrecioProveedorMaterial;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('proveedor')->orderByRaw("CAST(regexp_replace(codigo, '[^0-9]', '', 'g') AS INTEGER) ASC");

        if ($busqueda = $request->get('q')) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('categoria', 'like', "%{$busqueda}%");
            });
        }

        if ($request->get('tipo') === 'bodega') {
            $query->enBodega();
        } elseif ($request->get('tipo') === 'cotizable') {
            $query->soloCotizable();
        }

        if ($request->boolean('bajo_stock')) {
            $query->bajoStock();
        }

        $materiales = $query->paginate(15)->withQueryString();

        return view('inventario.materiales.index', [
            'materiales' => $materiales,
            'totalBajoStock' => Material::bajoStock()->where('activo', true)->count(),
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
        
        // Código manual o generado automáticamente
        $datos['codigo'] = !empty($datos['codigo']) ? trim($datos['codigo']) : Material::generarCodigo();
        $datos['en_bodega'] = $request->boolean('en_bodega');

        if (! $datos['en_bodega']) {
            $datos['stock'] = 0;
            $datos['stock_minimo'] = 0;
        }

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
        $datos['en_bodega'] = $request->boolean('en_bodega');
        unset($datos['stock']);

        if (! $datos['en_bodega']) {
            $datos['stock_minimo'] = 0;
        }

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

    public function precios(Material $material)
    {
        return view('inventario.materiales.precios', [
            'material' => $material,
            'precios' => $material->preciosProveedor()->with('proveedor')->orderBy('precio')->get(),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function guardarPrecio(Request $request, Material $material)
    {
        $datos = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'precio' => ['required', 'numeric', 'min:0'],
        ]);

        PrecioProveedorMaterial::updateOrCreate(
            ['material_id' => $material->id, 'proveedor_id' => $datos['proveedor_id']],
            ['precio' => $datos['precio'], 'actualizado_en' => now()]
        );

        return back()->with('status', 'Precio de proveedor guardado.');
    }

    public function eliminarPrecio(Material $material, PrecioProveedorMaterial $precio)
    {
        $precio->delete();

        return back()->with('status', 'Precio de proveedor eliminado.');
    }

    private function validarDatos(Request $request, ?int $idIgnorar = null): array
    {
        return $request->validate([
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('materiales', 'codigo')->ignore($idIgnorar),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'unidad_medida' => ['required', 'string', 'max:30'],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')],
            'en_bodega' => ['nullable', 'boolean'],
        ]);
    }
}