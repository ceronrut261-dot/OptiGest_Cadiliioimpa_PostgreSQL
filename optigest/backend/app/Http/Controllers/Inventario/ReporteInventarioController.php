<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteInventarioController extends Controller
{
    public function stock()
    {
        $materiales = Material::with('proveedor')->where('activo', true)
            ->orderBy('categoria')->orderBy('nombre')->get();

        $resumenPorCategoria = $materiales->groupBy('categoria')->map(function ($items) {
            return [
                'cantidad_items' => $items->count(),
                'valor_total' => $items->sum(fn ($m) => $m->precio * $m->stock),
                'bajo_stock' => $items->where('bajo_stock', true)->count(),
            ];
        });

        return view('inventario.reportes.stock', [
            'materiales' => $materiales,
            'materialesBajoStock' => $materiales->where('bajo_stock', true),
            'resumenPorCategoria' => $resumenPorCategoria,
            'valorTotalInventario' => $materiales->sum(fn ($m) => $m->precio * $m->stock),
        ]);
    }

    public function exportarPdf()
    {
        $materiales = Material::with('proveedor')->where('activo', true)
            ->orderBy('categoria')->orderBy('nombre')->get();

        $pdf = Pdf::loadView('inventario.reportes.stock-pdf', ['materiales' => $materiales, 'fecha' => now()]);

        return $pdf->download('reporte-inventario-'.now()->format('Y-m-d').'.pdf');
    }
}
