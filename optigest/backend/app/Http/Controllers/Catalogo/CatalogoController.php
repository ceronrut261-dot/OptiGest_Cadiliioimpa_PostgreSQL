<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $pestana = $request->get('tab', 'materiales');
        $busqueda = $request->get('q');
        $categoria = $request->get('categoria');

        // Categorías existentes para los filtros
        $categorias = Material::select('categoria')->distinct()->whereNotNull('categoria')->pluck('categoria');

        // 1. MATERIALES EN BODEGA (Únicamente los físicos con stock)
        $materialesQuery = Material::where('activo', true)
            ->where('en_bodega', true);

        if ($busqueda) {
            $materialesQuery->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }
        if ($categoria) {
            $materialesQuery->where('categoria', $categoria);
        }
        $materiales = $materialesQuery->orderBy('nombre')->paginate(12, ['*'], 'mat_page')->withQueryString();

        // 2. SERVICIOS Y MANO DE OBRA
        $servicios = CatalogoServicio::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get();

        // 3. CATÁLOGO DE PROVEEDORES (Materiales de compra / solo cotizables)
        $proveedoresQuery = Material::where('activo', true)
            ->where('en_bodega', false)
            ->with(['proveedor', 'preciosProveedor.proveedor']);

        if ($busqueda) {
            $proveedoresQuery->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }
        if ($categoria) {
            $proveedoresQuery->where('categoria', $categoria);
        }

        $materialesComparativa = $proveedoresQuery->orderBy('nombre')->paginate(12, ['*'], 'prov_page')->withQueryString();

        return view('catalogo.index', [
            'pestana' => $pestana,
            'busqueda' => $busqueda,
            'categoria' => $categoria,
            'categorias' => $categorias,
            'materiales' => $materiales,
            'servicios' => $servicios,
            'materialesComparativa' => $materialesComparativa,
        ]);
    }
}