<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $proveedor1 = Proveedor::firstOrCreate(
            ['nombre' => 'Ferreteria El Tornillo, S.A.'],
            ['nit' => '1234567-8', 'contacto' => 'Carlos Lopez', 'telefono' => '5511-2233', 'email' => 'ventas@eltornillo.gt', 'direccion' => 'Zona 1, Ciudad de Guatemala']
        );

        $proveedor2 = Proveedor::firstOrCreate(
            ['nombre' => 'Tuberias y Conexiones de Guatemala'],
            ['nit' => '9876543-2', 'contacto' => 'Maria Sosa', 'telefono' => '4422-9988', 'email' => 'contacto@tuconexiones.gt', 'direccion' => 'Mixco, Guatemala']
        );

        $materiales = [
            ['nombre' => 'Tuberia PVC 1/2" x 6m', 'categoria' => 'Tuberia', 'precio' => 35.00, 'stock' => 80, 'stock_minimo' => 15, 'proveedor_id' => $proveedor2->id],
            ['nombre' => 'Tuberia PVC 3/4" x 6m', 'categoria' => 'Tuberia', 'precio' => 48.50, 'stock' => 60, 'stock_minimo' => 15, 'proveedor_id' => $proveedor2->id],
            ['nombre' => 'Codo PVC 90 grados 1/2"', 'categoria' => 'Accesorios', 'precio' => 2.50, 'stock' => 200, 'stock_minimo' => 30, 'proveedor_id' => $proveedor2->id],
            ['nombre' => 'Llave de paso 1/2"', 'categoria' => 'Valvulas', 'precio' => 45.00, 'stock' => 25, 'stock_minimo' => 10, 'proveedor_id' => $proveedor1->id],
            ['nombre' => 'Cinta tefon 1/2"', 'categoria' => 'Sellantes', 'precio' => 4.00, 'stock' => 150, 'stock_minimo' => 25, 'proveedor_id' => $proveedor1->id],
            ['nombre' => 'Pegamento PVC 1/4 galon', 'categoria' => 'Sellantes', 'precio' => 38.00, 'stock' => 8, 'stock_minimo' => 10, 'proveedor_id' => $proveedor1->id],
            ['nombre' => 'Grifo monomando para lavamanos', 'categoria' => 'Griferia', 'precio' => 185.00, 'stock' => 6, 'stock_minimo' => 5, 'proveedor_id' => $proveedor1->id],
            ['nombre' => 'Bomba de agua 1/2 HP', 'categoria' => 'Bombeo', 'precio' => 950.00, 'stock' => 3, 'stock_minimo' => 4, 'proveedor_id' => $proveedor2->id],
        ];

        foreach ($materiales as $data) {
            Material::firstOrCreate(
                ['nombre' => $data['nombre']],
                array_merge($data, ['codigo' => Material::generarCodigo(), 'unidad_medida' => 'unidad'])
            );
        }
    }
}
