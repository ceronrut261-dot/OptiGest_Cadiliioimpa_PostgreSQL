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

        // Categorías existentes para los select de filtro
        $categorias = Material::select('categoria')->distinct()->whereNotNull('categoria')->pluck('categoria');

        // 1. Materiales de Bodega (Pestaña actual)
        $materialesQuery = Material::where('activo', true);
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

        // 2. Servicios (Pestaña actual)
        $servicios = CatalogoServicio::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get();

        // 3. Catálogo de Proveedores y Comparador de Precios (NUEVA PESTAÑA)
        $proveedoresQuery = Material::where('activo', true)
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