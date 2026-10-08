<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\PrecioProveedorMaterial;
use App\Models\Proveedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::withCount(['materiales', 'preciosProveedor'])->orderBy('nombre');

        if ($busqueda = $request->get('q')) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        }

        return view('proveedores.index', [
            'proveedores' => $query->paginate(15)->withQueryString()
        ]);
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

    public function catalogo(Proveedor $proveedor)
    {
        $precios = $proveedor->preciosProveedor()->with('material')->get();
        $materialesDisponibles = Material::where('activo', true)
            ->whereNotIn('id', $precios->pluck('material_id'))
            ->orderBy('nombre')
            ->get();

        return view('proveedores.catalogo', [
            'proveedor' => $proveedor,
            'precios' => $precios,
            'materialesDisponibles' => $materialesDisponibles,
        ]);
    }

    public function guardarMaterialCatalogo(Request $request, Proveedor $proveedor)
    {
        $datos = $request->validate([
            'material_id' => ['required', 'exists:materiales,id'],
            'precio' => ['required', 'numeric', 'min:0'],
        ]);

        PrecioProveedorMaterial::updateOrCreate(
            ['proveedor_id' => $proveedor->id, 'material_id' => $datos['material_id']],
            ['precio' => $datos['precio'], 'actualizado_en' => now()]
        );

        return back()->with('status', 'Material y precio asociados al proveedor.');
    }

    public function crearMaterialCatalogo(Request $request, Proveedor $proveedor)
    {
        $datos = $request->validate([
            'codigo' => ['nullable', 'string', 'max:50', 'unique:materiales,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'unidad_medida' => ['required', 'string', 'max:30'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $codigo = !empty($datos['codigo']) ? trim($datos['codigo']) : Material::generarCodigo();

        $material = Material::create([
            'codigo' => $codigo,
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'unidad_medida' => $datos['unidad_medida'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => $datos['precio'],
            'stock' => 0,
            'stock_minimo' => 0,
            'proveedor_id' => $proveedor->id,
            'activo' => true,
            'en_bodega' => false,
        ]);

        PrecioProveedorMaterial::create([
            'proveedor_id' => $proveedor->id,
            'material_id' => $material->id,
            'precio' => $datos['precio'],
            'actualizado_en' => now(),
        ]);

        return back()->with('status', "Material cotizable {$material->codigo} creado y vinculado a {$proveedor->nombre}.");
    }

    public function eliminarMaterialCatalogo(Proveedor $proveedor, PrecioProveedorMaterial $precio)
    {
        $precio->delete();

        return back()->with('status', 'Material removido del catálogo de este proveedor.');
    }

    /**
     * Muestra la vista interactiva para ingresar cantidades y calcular
     * el presupuesto estimado antes de enviar la cotización.
     */
    public function solicitudCotizacion(Proveedor $proveedor)
    {
        $precios = $proveedor->preciosProveedor()->with('material')->get();

        return view('proveedores.solicitud_cotizacion', compact('proveedor', 'precios'));
    }

    /**
     * Genera el PDF formal de la solicitud de cotización con los insumos seleccionados.
     */
    public function exportarSolicitudPdf(Request $request, Proveedor $proveedor)
    {
        $items = collect($request->input('items', []))->filter(function ($item) {
            return isset($item['cantidad']) && (float)$item['cantidad'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->with('status', 'Debes ingresar al menos una cantidad mayor a cero.');
        }

        $observaciones = $request->input('observaciones');
        $totalGeneral = $items->sum(function ($item) {
            return ((float)$item['cantidad']) * ((float)$item['precio']);
        });

        $pdf = Pdf::loadView('proveedores.pdf_solicitud', compact('proveedor', 'items', 'observaciones', 'totalGeneral'));

        return $pdf->stream("Solicitud_Cotizacion_{$proveedor->nombre}.pdf");
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nombre_contacto' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string'],
        ]);
    }
}