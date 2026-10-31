<?php

namespace Tests\Unit;

use App\Http\Controllers\Inventario\MaterialController;
use App\Models\Material;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MaterialControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('categoria');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->string('unidad_medida')->default('unidad');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('en_bodega')->default(true);
            $table->timestamps();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Material::unsetEventDispatcher();
    }

    protected function tearDown(): void
    {
        Material::setEventDispatcher(app('events'));

        parent::tearDown();
    }

    private function enviar(array $datos)
    {
        $request = Request::create(
            '/inventario/materiales',
            'POST',
            $datos
        );

        return app(MaterialController::class)->store($request);
    }

    private function datosValidos(array $sobrescribir = []): array
    {
        return array_merge([
            'codigo' => 'MAT-TEST-001',
            'nombre' => 'Tubería de prueba',
            'categoria' => 'Tubería',
            'descripcion' => 'Material de prueba',
            'precio' => 25.50,
            'stock' => 20,
            'stock_minimo' => 5,
            'unidad_medida' => 'unidad',
            'en_bodega' => '1',
            'activo' => true,
        ], $sobrescribir);
    }

    public function test_registra_material_valido(): void
    {
        $respuesta = $this->enviar($this->datosValidos());

        $this->assertDatabaseHas('materiales', [
            'codigo' => 'MAT-TEST-001',
            'nombre' => 'Tubería de prueba',
            'stock' => 20,
            'stock_minimo' => 5,
            'en_bodega' => 1,
        ], 'sqlite');

        $this->assertSame(
            route('inventario.materiales.index'),
            $respuesta->getTargetUrl()
        );
    }

    public function test_genera_codigo_si_no_se_proporciona(): void
    {
        $datos = $this->datosValidos();
        $datos['codigo'] = '';

        $this->enviar($datos);

        $this->assertDatabaseHas('materiales', [
            'codigo' => 'MAT-00001',
        ], 'sqlite');
    }

    public function test_material_fuera_de_bodega_se_guarda_sin_stock(): void
    {
        $this->enviar($this->datosValidos([
            'codigo' => 'MAT-TEST-002',
            'en_bodega' => '0',
        ]));

        $this->assertDatabaseHas('materiales', [
            'codigo' => 'MAT-TEST-002',
            'stock' => 0,
            'stock_minimo' => 0,
            'en_bodega' => 0,
        ], 'sqlite');
    }

    public function test_rechaza_nombre_con_caracteres_no_permitidos(): void
    {
        try {
            $this->enviar($this->datosValidos([
                'nombre' => '<script>',
            ]));

            $this->fail('Debió rechazarse el nombre inválido.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('nombre', $e->errors());
        }

        $this->assertSame(0, DB::table('materiales')->count());
    }

    public function test_rechaza_codigo_con_caracteres_no_permitidos(): void
    {
        try {
            $this->enviar($this->datosValidos([
                'codigo' => 'MAT TEST!',
            ]));

            $this->fail('Debió rechazarse el código inválido.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('codigo', $e->errors());
        }

        $this->assertSame(0, DB::table('materiales')->count());
    }

    public function test_rechaza_precio_negativo(): void
    {
        try {
            $this->enviar($this->datosValidos([
                'precio' => -1,
            ]));

            $this->fail('Debió rechazarse el precio negativo.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('precio', $e->errors());
        }

        $this->assertSame(0, DB::table('materiales')->count());
    }

    public function test_rechaza_codigo_duplicado(): void
    {
        Material::create($this->datosValidos());

        try {
            $this->enviar($this->datosValidos([
                'nombre' => 'Otro material',
            ]));

            $this->fail('Debió rechazarse el código duplicado.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('codigo', $e->errors());
        }

        $this->assertSame(1, DB::table('materiales')->count());
    }

    public function test_actualiza_material_sin_modificar_stock(): void
    {
        $material = Material::create($this->datosValidos());

        $request = Request::create(
            '/inventario/materiales/'.$material->id,
            'PUT',
            $this->datosValidos([
                'codigo' => 'MAT-TEST-001',
                'nombre' => 'Tubería actualizada',
                'stock' => 99,
            ])
        );

        app(MaterialController::class)->update($request, $material);

        $this->assertSame(
            'Tubería actualizada',
            $material->fresh()->nombre
        );

        $this->assertSame(20, $material->fresh()->stock);
    }

    public function test_desactiva_material_sin_eliminarlo(): void
    {
        $material = Material::create($this->datosValidos());

        app(MaterialController::class)->destroy($material);

        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'activo' => 0,
        ], 'sqlite');

        $this->assertSame(1, DB::table('materiales')->count());
    }
}
