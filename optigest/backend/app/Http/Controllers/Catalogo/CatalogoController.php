<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\Material;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $vista = $request->get('vista') === 'servicios' ? 'servicios' : 'materiales';
        $buscar = trim((string) $request->get('q', ''));
        $categoria = $request->get('categoria');

        $categorias = Material::where('activo', true)
            ->whereNotNull('categoria')->where('categoria', '<>', '')
            ->distinct()->orderBy('categoria')->pluck('categoria');

        $materiales = Material::where('activo', true)
            ->when($buscar !== '' && $vista === 'materiales', function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%")
                        ->orWhere('codigo', 'ILIKE', "%{$buscar}%")
                        ->orWhere('descripcion', 'ILIKE', "%{$buscar}%");
                });
            })
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->orderBy('nombre')
            ->paginate(24)
            ->withQueryString();

        $puedeGestionar = $request->user()->hasRole('administrador');

        $servicios = CatalogoServicio::when(! $puedeGestionar, fn ($q) => $q->where('activo', true))
            ->when($buscar !== '' && $vista === 'servicios', function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%")
                        ->orWhere('codigo', 'ILIKE', "%{$buscar}%")
                        ->orWhere('descripcion', 'ILIKE', "%{$buscar}%");
                });
            })
            ->orderBy('categoria')->orderBy('nombre')
            ->get();

        return view('catalogo.index', compact('vista', 'buscar', 'categoria', 'categorias', 'materiales', 'servicios', 'puedeGestionar'));
    }
}
