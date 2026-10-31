<?php

namespace Tests\Unit;

use App\Models\HistorialPrecioMaterial;
use App\Models\PrecioProveedorMaterial;
use App\Services\HistorialPrecioProveedorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HistorialPrecioProveedorServiceTest extends TestCase
{
    private int $materialId;
    private int $proveedorId;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->default('MAT-00001');
            $table->string('nombre')->default('Material de prueba');
            $table->string('categoria')->default('Prueba');
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->string('unidad_medida')->default('unidad');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('precios_proveedor_material', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('precio', 10, 2);
            $table->timestamp('actualizado_en');
            $table->timestamps();

            $table->unique(
                ['material_id', 'proveedor_id'],
                'precios_material_proveedor_unico'
            );
        });

        Schema::create('historial_precios_materiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->decimal('precio_anterior', 10, 2);
            $table->decimal('precio_nuevo', 10, 2);
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamp('fecha');
            $table->timestamps();
        });

        $this->proveedorId = DB::table('proveedores')->insertGetId([
            'nombre' => 'Proveedor de prueba',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->materialId = DB::table('materiales')->insertGetId([
            'codigo' => 'MAT-00001',
            'nombre' => 'Material de prueba',
            'categoria' => 'Prueba',
            'precio' => 0,
            'stock' => 0,
            'stock_minimo' => 10,
            'unidad_medida' => 'unidad',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_registra_el_precio_inicial_y_su_historial(): void
    {
        $registro = HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        $this->assertDatabaseCount('precios_proveedor_material', 1);
        $this->assertDatabaseCount('historial_precios_materiales', 1);

        $this->assertSame(125.50, (float) $registro->precio);

        $historial = HistorialPrecioMaterial::firstOrFail();

        $this->assertSame(125.50, (float) $historial->precio_anterior);
        $this->assertSame(125.50, (float) $historial->precio_nuevo);
        $this->assertSame(
            'Precio inicial del proveedor',
            $historial->motivo
        );
    }

    public function test_registra_el_cambio_de_precio_en_el_historial(): void
    {
        HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        $registro = HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            140.75
        );

        $this->assertDatabaseCount('precios_proveedor_material', 1);
        $this->assertDatabaseCount('historial_precios_materiales', 2);
        $this->assertSame(140.75, (float) $registro->precio);

        $historial = HistorialPrecioMaterial::orderByDesc('id')
            ->firstOrFail();

        $this->assertSame(125.50, (float) $historial->precio_anterior);
        $this->assertSame(140.75, (float) $historial->precio_nuevo);
        $this->assertSame(
            'Actualización de precio del proveedor',
            $historial->motivo
        );
    }

    public function test_no_agrega_historial_si_el_precio_no_cambia(): void
    {
        HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        $this->assertDatabaseCount('precios_proveedor_material', 1);
        $this->assertDatabaseCount('historial_precios_materiales', 1);
    }

    public function test_no_duplica_el_registro_del_mismo_material_y_proveedor(): void
    {
        HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        HistorialPrecioProveedorService::guardar(
            $this->materialId,
            $this->proveedorId,
            125.50
        );

        $cantidad = PrecioProveedorMaterial::where(
            'material_id',
            $this->materialId
        )->where(
            'proveedor_id',
            $this->proveedorId
        )->count();

        $this->assertSame(1, $cantidad);
    }
}
