<?php

namespace Tests\Unit;

use App\Http\Controllers\Proveedores\ProveedorController;
use App\Models\Proveedor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProveedorControllerTest extends TestCase
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

        // Tabla de proveedores para las pruebas.
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit', 20)->nullable();
            $table->string('contacto')->nullable();
            $table->string('nombre_contacto', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de auditoría para las operaciones del proveedor.
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion', 50);
            $table->string('modulo', 100);
            $table->string('registro_tipo', 150)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Proveedor de prueba',
            'telefono' => '55551234',
            'nombre_contacto' => 'Contacto Prueba',
            'descripcion' => 'Proveedor para pruebas',
        ], $cambios);
    }

    private function enviar(array $datos)
    {
        $request = Request::create(
            '/proveedores',
            'POST',
            $datos
        );

        return app(ProveedorController::class)->store($request);
    }

    public function test_registra_proveedor_valido(): void
    {
        $respuesta = $this->enviar($this->datosValidos());

        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Proveedor de prueba',
            'telefono' => '55551234',
            'activo' => 1,
        ], 'sqlite');

        $this->assertSame(
            route('proveedores.index'),
            $respuesta->getTargetUrl()
        );
    }

    public function test_rechaza_nombre_vacio(): void
    {
        try {
            $this->enviar($this->datosValidos([
                'nombre' => '',
            ]));

            $this->fail('Debió rechazarse el nombre vacío.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('nombre', $e->errors());
        }

        $this->assertSame(0, DB::table('proveedores')->count());
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

        $this->assertSame(0, DB::table('proveedores')->count());
    }

    public function test_rechaza_telefono_con_letras(): void
    {
        try {
            $this->enviar($this->datosValidos([
                'telefono' => 'abc12345',
            ]));

            $this->fail('Debió rechazarse el teléfono inválido.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('telefono', $e->errors());
        }

        $this->assertSame(0, DB::table('proveedores')->count());
    }

    public function test_actualiza_proveedor(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor anterior',
            'telefono' => '55551234',
            'activo' => true,
        ]);

        $request = Request::create(
            '/proveedores/'.$proveedor->id,
            'PUT',
            $this->datosValidos([
                'nombre' => 'Proveedor actualizado',
            ])
        );

        $respuesta = app(ProveedorController::class)
            ->update($request, $proveedor);

        $this->assertSame(
            'Proveedor actualizado',
            $proveedor->fresh()->nombre
        );

        $this->assertSame(
            route('proveedores.index'),
            $respuesta->getTargetUrl()
        );
    }

    public function test_desactiva_proveedor_sin_eliminarlo(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor para desactivar',
            'activo' => true,
        ]);

        app(ProveedorController::class)->destroy($proveedor);

        $this->assertDatabaseHas('proveedores', [
            'id' => $proveedor->id,
            'activo' => 0,
        ], 'sqlite');

        $this->assertSame(1, DB::table('proveedores')->count());
    }
}
