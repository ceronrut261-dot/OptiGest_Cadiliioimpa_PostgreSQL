<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MovimientoInventario;
use App\Models\SalidaMaterial;
use App\Models\SalidaMaterialDetalle;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalidasMaterialesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->crearTablas();

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        Role::create([
            'name' => 'administrador',
            'guard_name' => 'web',
        ]);

        Role::create([
            'name' => 'tecnico',
            'guard_name' => 'web',
        ]);

        Role::create([
            'name' => 'cotizador',
            'guard_name' => 'web',
        ]);
    }

    private function crearTablas(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion');
            $table->string('modulo')->nullable();
            $table->string('registro_tipo')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('valores_anteriores')->nullable();
            $table->text('valores_nuevos')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Tabla necesaria para el registro automático de precios.
        Schema::create('historial_precios_materiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->decimal('precio_anterior', 10, 2)->nullable();
            $table->decimal('precio_nuevo', 10, 2);
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamp('fecha')->nullable();
            $table->timestamps();
        });

        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('categoria')->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('unidad_medida')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('en_bodega')->default(true);
            $table->timestamps();
        });

        Schema::create('salidas_materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('usuario_id');
            $table->string('proyecto');
            $table->string('persona_recibe');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->timestamp('fecha')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('falta_comprar')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado_entrega')->default('pendiente');
            $table->unsignedBigInteger('confirmado_por')->nullable();
            $table->timestamp('confirmado_en')->nullable();
            $table->text('observaciones_entrega')->nullable();
            $table->unsignedBigInteger('salida_original_id')->nullable();
            $table->timestamps();
        });

        Schema::create('salida_material_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salida_id');
            $table->unsignedBigInteger('material_id');
            $table->string('descripcion');
            $table->integer('cantidad');
            $table->integer('cantidad_entregada')->default(0);
            $table->decimal('valor_unitario', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->string('tipo');
            $table->integer('cantidad');
            $table->string('motivo')->nullable();
            $table->timestamp('fecha')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->unsignedBigInteger('salida_id')->nullable();
            $table->integer('stock_resultante')->nullable();
            $table->timestamps();
        });
    }

    private function crearUsuario(string $rol = 'administrador'): User
    {
        $usuario = User::create([
            'name' => 'Usuario de prueba',
            'email' => uniqid() . '@optigest.test',
            'password' => 'password',
            'activo' => true,
        ]);

        $usuario->assignRole($rol);

        return $usuario;
    }

    private function crearMaterial(int $stock = 20): Material
    {
        return Material::create([
            'codigo' => 'MAT-001',
            'nombre' => 'Tubería PVC',
            'categoria' => 'Tubería',
            'precio' => 25,
            'stock' => $stock,
            'stock_minimo' => 2,
            'unidad_medida' => 'Unidad',
            'activo' => true,
            'en_bodega' => true,
        ]);
    }

    private function crearVale(
        User $usuario,
        Material $material,
        int $cantidad = 10
    ): SalidaMaterial {
        $salida = SalidaMaterial::create([
            'codigo' => 'SAL-00001',
            'usuario_id' => $usuario->id,
            'proyecto' => 'Proyecto de prueba',
            'persona_recibe' => 'Juan Perez',
            'fecha' => now(),
            'total' => $cantidad * $material->precio,
            'estado_entrega' => 'pendiente',
        ]);

        SalidaMaterialDetalle::create([
            'salida_id' => $salida->id,
            'material_id' => $material->id,
            'descripcion' => $material->nombre,
            'cantidad' => $cantidad,
            'cantidad_entregada' => 0,
            'valor_unitario' => $material->precio,
            'total' => $cantidad * $material->precio,
        ]);

        return $salida;
    }

    public function test_confirmacion_completa_no_devuelve_materiales(): void
    {
        $usuario = $this->crearUsuario('administrador');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [
                    $salida->detalles()->first()->id => 5,
                ],
            ])
            ->assertRedirect(route('salidas.index'));

        $this->assertDatabaseHas('salidas_materiales', [
            'id' => $salida->id,
            'estado_entrega' => 'confirmada',
            'confirmado_por' => $usuario->id,
        ]);

        $this->assertDatabaseHas('salida_material_detalle', [
            'salida_id' => $salida->id,
            'cantidad_entregada' => 5,
        ]);

        $this->assertSame(10, (int) $material->fresh()->stock);

        $this->assertDatabaseCount('movimientos_inventario', 0);
    }

    public function test_entrega_parcial_devuelve_la_diferencia_al_inventario(): void
    {
        $usuario = $this->crearUsuario('tecnico');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [
                    $salida->detalles()->first()->id => 3,
                ],
            ])
            ->assertRedirect(route('salidas.index'));

        $this->assertSame(12, (int) $material->fresh()->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'material_id' => $material->id,
            'tipo' => MovimientoInventario::TIPO_ENTRADA,
            'cantidad' => 2,
            'salida_id' => $salida->id,
            'stock_resultante' => 12,
        ]);
    }

    public function test_cero_entregado_devuelve_toda_la_cantidad(): void
    {
        $usuario = $this->crearUsuario('administrador');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [
                    $salida->detalles()->first()->id => 0,
                ],
            ])
            ->assertRedirect(route('salidas.index'));

        $this->assertSame(15, (int) $material->fresh()->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'material_id' => $material->id,
            'tipo' => MovimientoInventario::TIPO_ENTRADA,
            'cantidad' => 5,
            'salida_id' => $salida->id,
            'stock_resultante' => 15,
        ]);
    }

    public function test_no_permite_entregar_mas_de_lo_solicitado(): void
    {
        $usuario = $this->crearUsuario('administrador');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [
                    $salida->detalles()->first()->id => 8,
                ],
            ])
            ->assertSessionHasErrors();

        $this->assertSame('pendiente', $salida->fresh()->estado_entrega);
        $this->assertSame(10, (int) $material->fresh()->stock);
    }

    public function test_no_permite_confirmar_dos_veces_el_mismo_vale(): void
    {
        $usuario = $this->crearUsuario('administrador');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $detalleId = $salida->detalles()->first()->id;

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [$detalleId => 3],
            ])
            ->assertRedirect(route('salidas.index'));

        $this->patch(route('salidas.confirmar.guardar', $salida->fresh()), [
            'entregadas' => [$detalleId => 3],
        ])->assertSessionHasErrors();

        $this->assertSame(12, (int) $material->fresh()->stock);

        $this->assertDatabaseCount('movimientos_inventario', 1);
    }

    public function test_cotizador_no_puede_confirmar_entregas(): void
    {
        $usuario = $this->crearUsuario('cotizador');
        $material = $this->crearMaterial(10);
        $salida = $this->crearVale($usuario, $material, 5);

        $this->actingAs($usuario)
            ->patch(route('salidas.confirmar.guardar', $salida), [
                'entregadas' => [
                    $salida->detalles()->first()->id => 5,
                ],
            ])
            ->assertForbidden();

        $this->assertSame('pendiente', $salida->fresh()->estado_entrega);
        $this->assertSame(10, (int) $material->fresh()->stock);
    }
}
