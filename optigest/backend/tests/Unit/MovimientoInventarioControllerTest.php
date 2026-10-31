<?php

namespace Tests\Unit;

use App\Http\Controllers\Inventario\MovimientoInventarioController;
use App\Models\Material;
use App\Models\MovimientoInventario;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoInventarioControllerTest extends TestCase
{
    private User $usuario;
    private Material $material;

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
            $table->string('codigo')->default('MAT-00001');
            $table->string('nombre');
            $table->string('categoria')->default('General');
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

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->string('tipo');
            $table->unsignedInteger('cantidad');
            $table->string('motivo')->nullable();
            $table->dateTime('fecha');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->unsignedBigInteger('salida_id')->nullable();
            $table->unsignedInteger('stock_resultante');
            $table->timestamps();
        });

        // Evita que el observador del material requiera otras tablas.
        Material::unsetEventDispatcher();

        $this->usuario = new User();
        $this->usuario->id = 1;

        $this->material = Material::create([
            'codigo' => 'MAT-00001',
            'nombre' => 'Tubería de prueba',
            'categoria' => 'Tubería',
            'precio' => 10,
            'stock' => 20,
            'stock_minimo' => 5,
            'unidad_medida' => 'unidad',
            'activo' => true,
            'en_bodega' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Material::setEventDispatcher(app('events'));

        parent::tearDown();
    }

    private function enviarMovimiento(
        string $tipo,
        int $cantidad,
        ?string $motivo = 'Prueba automatizada'
    ) {
        $request = Request::create(
            '/inventario/movimientos',
            'POST',
            [
                'material_id' => $this->material->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
            ]
        );

        $request->setUserResolver(fn () => $this->usuario);

        return app(MovimientoInventarioController::class)->store($request);
    }

    public function test_entrada_incrementa_stock_y_registra_movimiento(): void
    {
        $respuesta = $this->enviarMovimiento('entrada', 10);

        $this->assertSame(30, $this->material->fresh()->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'material_id' => $this->material->id,
            'tipo' => 'entrada',
            'cantidad' => 10,
            'usuario_id' => 1,
            'stock_resultante' => 30,
        ], 'sqlite');

        $this->assertSame(
            route('inventario.movimientos.index'),
            $respuesta->getTargetUrl()
        );
    }

    public function test_salida_valida_disminuye_stock_y_registra_movimiento(): void
    {
        $this->enviarMovimiento('salida', 5);

        $this->assertSame(15, $this->material->fresh()->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'material_id' => $this->material->id,
            'tipo' => 'salida',
            'cantidad' => 5,
            'stock_resultante' => 15,
        ], 'sqlite');
    }

    public function test_rechaza_salida_cuando_stock_es_igual_al_minimo(): void
    {
        $this->material->stock = 5;
        $this->material->save();

        try {
            $this->enviarMovimiento('salida', 1);
            $this->fail('Debió rechazarse la salida por stock mínimo.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('material_id', $e->errors());
        }

        $this->assertSame(5, $this->material->fresh()->stock);
        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }

    public function test_rechaza_salida_cuando_stock_es_menor_al_minimo(): void
    {
        $this->material->stock = 4;
        $this->material->save();

        try {
            $this->enviarMovimiento('salida', 1);
            $this->fail('Debió rechazarse la salida por stock bajo.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('material_id', $e->errors());
        }

        $this->assertSame(4, $this->material->fresh()->stock);
        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }

    public function test_rechaza_salida_mayor_al_stock_disponible(): void
    {
        try {
            $this->enviarMovimiento('salida', 21);
            $this->fail('Debió rechazarse la salida por stock insuficiente.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cantidad', $e->errors());
        }

        $this->assertSame(20, $this->material->fresh()->stock);
        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }

    public function test_rechaza_cantidad_cero(): void
    {
        try {
            $this->enviarMovimiento('entrada', 0);
            $this->fail('Debió rechazarse una cantidad igual a cero.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cantidad', $e->errors());
        }

        $this->assertSame(20, $this->material->fresh()->stock);
        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }

    public function test_rechaza_tipo_de_movimiento_invalido(): void
    {
        try {
            $this->enviarMovimiento('ajuste', 1);
            $this->fail('Debió rechazarse el tipo de movimiento.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tipo', $e->errors());
        }

        $this->assertSame(20, $this->material->fresh()->stock);
        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }
}
