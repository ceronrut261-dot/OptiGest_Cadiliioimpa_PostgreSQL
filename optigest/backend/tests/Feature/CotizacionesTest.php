<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\Cotizacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CotizacionesTest extends TestCase
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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

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

        Schema::create('historial_precios_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id');
            $table->decimal('precio_anterior', 12, 2)->nullable();
            $table->decimal('precio_nuevo', 12, 2);
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('motivo')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->timestamps();
        });

        Schema::create('catalogo_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable();
            $table->string('unidad')->default('servicio');
            $table->decimal('precio_estandar', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('cliente_id');
            $table->text('descripcion');
            $table->string('prioridad')->default('media');
            $table->string('estado')->default('pendiente');
            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->timestamp('fecha_programada')->nullable();
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamps();
        });

        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('cotizador_id');
            $table->string('estado')->default('borrador');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('fecha');
            $table->text('observaciones')->nullable();
            $table->decimal('mano_obra_total', 12, 2)->default(0);
            $table->decimal('gastos_adicionales_total', 12, 2)->default(0);
            $table->boolean('iva_aplicado')->default(false);
            $table->decimal('iva_monto', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cotizacion_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('material_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('cotizacion_servicios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('servicio_id')->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 8, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('ticket_gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('salida_id')->nullable();
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->string('descripcion');
            $table->string('comercio')->nullable();
            $table->string('tipo_documento')->default('factura');
            $table->string('numero_documento')->nullable();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_gasto');
            $table->boolean('cobrar_al_cliente')->default(true);
            $table->string('archivo_path')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_mime')->nullable();
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->timestamps();
        });

        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion');
            $table->string('modulo');
            $table->string('registro_tipo')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['administrador', 'tecnico', 'cotizador'] as $nombre) {
            Role::firstOrCreate([
                'name' => $nombre,
                'guard_name' => 'web',
            ]);
        }
    }

    private function crearUsuario(): User
    {
        $usuario = User::create([
            'name' => 'Cotizador de prueba',
            'email' => 'cotizador@optigest.test',
            'password' => Hash::make('password'),
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        $usuario->assignRole('cotizador');

        return $usuario;
    }

    private function crearCliente(): Cliente
    {
        return Cliente::create([
            'nombre' => 'Cliente de prueba',
            'telefono' => '55555555',
            'direccion' => 'Guatemala',
            'email' => 'cliente@optigest.test',
            'activo' => true,
        ]);
    }

    private function crearMaterial(): Material
    {
        return Material::create([
            'codigo' => 'MAT-00001',
            'nombre' => 'Tubería PVC',
            'categoria' => 'Tuberías',
            'precio' => 50,
            'stock' => 20,
            'stock_minimo' => 5,
            'unidad_medida' => 'unidad',
            'activo' => true,
            'en_bodega' => true,
        ]);
    }

    private function datosCotizacion(int $clienteId, int $materialId): array
    {
        return [
            'cliente_id' => $clienteId,
            'materiales' => [
                [
                    'id' => $materialId,
                    'cantidad' => 2,
                ],
            ],
        ];
    }

    private function crearCotizacionDePrueba(User $usuario): Cotizacion
    {
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        )->assertRedirect();

        return Cotizacion::firstOrFail();
    }

    private function agregarMovimientoInventario(Cotizacion $cotizacion): void
    {
        DB::table('movimientos_inventario')->insert([
            'cotizacion_id' => $cotizacion->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_cotizador_puede_crear_cotizacion_con_materiales(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $respuesta = $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        );

        $respuesta->assertRedirect();

        $this->assertDatabaseHas('cotizaciones', [
            'cliente_id' => $cliente->id,
            'cotizador_id' => $usuario->id,
            'estado' => 'borrador',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $this->assertDatabaseHas('cotizacion_detalle', [
            'material_id' => $material->id,
            'cantidad' => 2,
            'precio_unitario' => 50,
            'subtotal' => 100,
        ]);
    }

    public function test_cotizacion_calcula_iva_correctamente(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            array_merge(
                $this->datosCotizacion($cliente->id, $material->id),
                ['aplicar_iva' => true]
            )
        )->assertRedirect();

        $cotizacion = Cotizacion::firstOrFail();

        $tasaIva = (float) config('optigest.iva', 0.12);
        $ivaEsperado = round(100 * $tasaIva, 2);

        $this->assertEquals(100, (float) $cotizacion->subtotal);
        $this->assertEquals($ivaEsperado, (float) $cotizacion->iva_monto);
        $this->assertEquals(100 + $ivaEsperado, (float) $cotizacion->total);
        $this->assertTrue($cotizacion->iva_aplicado);
    }

    public function test_no_permite_crear_cotizacion_sin_contenido(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();

        $respuesta = $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            ['cliente_id' => $cliente->id]
        );

        $respuesta->assertSessionHasErrors('materiales');

        $this->assertDatabaseCount('cotizaciones', 0);
    }

    public function test_aprobar_cotizacion_no_descuenta_stock(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        );

        $cotizacion = Cotizacion::firstOrFail();

        $this->actingAs($usuario)
            ->patch(route('cotizaciones.aprobar', $cotizacion))
            ->assertRedirect(route('cotizaciones.show', $cotizacion));

        $this->assertDatabaseHas('cotizaciones', [
            'id' => $cotizacion->id,
            'estado' => 'aprobada',
        ]);

        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'stock' => 20,
        ]);
    }

    public function test_rechazar_cotizacion_cambia_su_estado(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        );

        $cotizacion = Cotizacion::firstOrFail();

        $this->actingAs($usuario)
            ->patch(route('cotizaciones.rechazar', $cotizacion))
            ->assertRedirect(route('cotizaciones.show', $cotizacion));

        $this->assertDatabaseHas('cotizaciones', [
            'id' => $cotizacion->id,
            'estado' => 'rechazada',
        ]);
    }

    public function test_cotizador_puede_editar_cotizacion_en_borrador(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        );

        $cotizacion = Cotizacion::firstOrFail();

        $datosActualizados = [
            'cliente_id' => $cliente->id,
            'observaciones' => 'Cotización actualizada para pruebas',
            'materiales' => [
                [
                    'id' => $material->id,
                    'cantidad' => 3,
                ],
            ],
        ];

        $respuesta = $this->actingAs($usuario)->put(
            route('cotizaciones.update', $cotizacion),
            $datosActualizados
        );

        $respuesta->assertRedirect(route('cotizaciones.show', $cotizacion));

        $cotizacion->refresh();

        $this->assertEquals(150, (float) $cotizacion->subtotal);
        $this->assertEquals(150, (float) $cotizacion->total);
        $this->assertEquals(
            'Cotización actualizada para pruebas',
            $cotizacion->observaciones
        );

        $this->assertDatabaseHas('cotizacion_detalle', [
            'cotizacion_id' => $cotizacion->id,
            'material_id' => $material->id,
            'cantidad' => 3,
            'precio_unitario' => 50,
            'subtotal' => 150,
        ]);

        $this->assertDatabaseCount('cotizacion_detalle', 1);
    }

    public function test_no_permite_editar_cotizacion_aprobada(): void
    {
        $usuario = $this->crearUsuario();
        $cliente = $this->crearCliente();
        $material = $this->crearMaterial();

        $this->actingAs($usuario)->post(
            route('cotizaciones.store'),
            $this->datosCotizacion($cliente->id, $material->id)
        );

        $cotizacion = Cotizacion::firstOrFail();

        $cotizacion->update([
            'estado' => 'aprobada',
        ]);

        $datosActualizados = [
            'cliente_id' => $cliente->id,
            'observaciones' => 'Este cambio no debe guardarse',
            'materiales' => [
                [
                    'id' => $material->id,
                    'cantidad' => 5,
                ],
            ],
        ];

        $respuesta = $this->actingAs($usuario)->put(
            route('cotizaciones.update', $cotizacion),
            $datosActualizados
        );

        $respuesta->assertSessionHasErrors('cotizacion');

        $cotizacion->refresh();

        $this->assertEquals('aprobada', $cotizacion->estado);
        $this->assertEquals(100, (float) $cotizacion->subtotal);
        $this->assertEquals(100, (float) $cotizacion->total);

        $this->assertDatabaseHas('cotizacion_detalle', [
            'cotizacion_id' => $cotizacion->id,
            'material_id' => $material->id,
            'cantidad' => 2,
            'precio_unitario' => 50,
            'subtotal' => 100,
        ]);

        $this->assertDatabaseCount('cotizacion_detalle', 1);
    }

    public function test_puede_eliminar_cotizacion_sin_movimientos_inventario(): void
    {
        $usuario = $this->crearUsuario();
        $cotizacion = $this->crearCotizacionDePrueba($usuario);

        $respuesta = $this->actingAs($usuario)->delete(
            route('cotizaciones.destroy', $cotizacion)
        );

        $respuesta->assertRedirect(route('cotizaciones.index'));

        $this->assertDatabaseMissing('cotizaciones', [
            'id' => $cotizacion->id,
        ]);
    }

    public function test_no_permite_eliminar_cotizacion_con_movimientos_inventario(): void
    {
        $usuario = $this->crearUsuario();
        $cotizacion = $this->crearCotizacionDePrueba($usuario);

        $this->agregarMovimientoInventario($cotizacion);

        $respuesta = $this->actingAs($usuario)->delete(
            route('cotizaciones.destroy', $cotizacion)
        );

        $respuesta->assertSessionHasErrors('cotizacion');

        $this->assertDatabaseHas('cotizaciones', [
            'id' => $cotizacion->id,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'cotizacion_id' => $cotizacion->id,
        ]);
    }

    public function test_puede_revertir_cotizacion_aprobada_a_borrador(): void
    {
        $usuario = $this->crearUsuario();
        $cotizacion = $this->crearCotizacionDePrueba($usuario);

        $cotizacion->update([
            'estado' => 'aprobada',
        ]);

        $respuesta = $this->actingAs($usuario)->patch(
            route('cotizaciones.revertir', $cotizacion)
        );

        $respuesta->assertRedirect(route('cotizaciones.show', $cotizacion));

        $cotizacion->refresh();

        $this->assertEquals('borrador', $cotizacion->estado);
    }

    public function test_no_permite_revertir_cotizacion_con_movimientos_inventario(): void
    {
        $usuario = $this->crearUsuario();
        $cotizacion = $this->crearCotizacionDePrueba($usuario);

        $cotizacion->update([
            'estado' => 'aprobada',
        ]);

        $this->agregarMovimientoInventario($cotizacion);

        $respuesta = $this->actingAs($usuario)->patch(
            route('cotizaciones.revertir', $cotizacion)
        );

        $respuesta->assertSessionHasErrors('cotizacion');

        $cotizacion->refresh();

        $this->assertEquals('aprobada', $cotizacion->estado);

        $this->assertDatabaseHas('movimientos_inventario', [
            'cotizacion_id' => $cotizacion->id,
        ]);
    }
}
