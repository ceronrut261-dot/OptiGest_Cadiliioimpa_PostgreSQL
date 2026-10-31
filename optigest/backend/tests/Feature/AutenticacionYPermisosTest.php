<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AutenticacionYPermisosTest extends TestCase
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

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit', 20)->nullable();
            $table->string('contacto')->nullable();
            $table->string('nombre_contacto')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->string('categoria', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_minimo')->default(10);
            $table->string('unidad_medida', 30)->default('unidad');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('en_bodega')->default(true);
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

            $table->primary([
                'role_id',
                'model_id',
                'model_type',
            ]);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type']);

            $table->primary([
                'permission_id',
                'model_id',
                'model_type',
            ]);
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

    protected function crearUsuario(string $rol): User
    {
        $usuario = User::create([
            'name' => 'Usuario de prueba',
            'email' => $rol.'@optigest.test',
            'password' => 'password',
            'activo' => true,
        ]);

        $usuario->assignRole($rol);

        return $usuario;
    }

    // AUTENTICACIÓN

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('inventario.materiales.index'))
            ->assertRedirect(route('login'));
    }

    // PERMISOS DE MATERIALES

    public function test_tecnico_no_puede_acceder_a_materiales(): void
    {
        $usuario = $this->crearUsuario('tecnico');

        $this->actingAs($usuario)
            ->get(route('inventario.materiales.index'))
            ->assertForbidden();
    }

    public function test_administrador_puede_acceder_a_materiales(): void
    {
        $usuario = $this->crearUsuario('administrador');

        $this->actingAs($usuario)
            ->get(route('inventario.materiales.index'))
            ->assertOk()
            ->assertSee('Catálogo de Materiales');
    }

    public function test_cotizador_puede_acceder_a_materiales(): void
    {
        $usuario = $this->crearUsuario('cotizador');

        $this->actingAs($usuario)
            ->get(route('inventario.materiales.index'))
            ->assertOk()
            ->assertSee('Catálogo de Materiales');
    }

    // PERMISOS DE PROVEEDORES

    public function test_tecnico_no_puede_acceder_a_proveedores(): void
    {
        $usuario = $this->crearUsuario('tecnico');

        $this->actingAs($usuario)
            ->get(route('proveedores.index'))
            ->assertForbidden();
    }

    public function test_administrador_puede_acceder_a_proveedores(): void
    {
        $usuario = $this->crearUsuario('administrador');

        $this->actingAs($usuario)
            ->get(route('proveedores.index'))
            ->assertOk();
    }

    public function test_cotizador_puede_acceder_a_proveedores(): void
    {
        $usuario = $this->crearUsuario('cotizador');

        $this->actingAs($usuario)
            ->get(route('proveedores.index'))
            ->assertOk();
    }

    // PERMISOS DE AUDITORÍA

    public function test_cotizador_no_puede_acceder_a_auditoria(): void
    {
        $usuario = $this->crearUsuario('cotizador');

        $this->actingAs($usuario)
            ->get(route('auditoria.index'))
            ->assertForbidden();
    }

    public function test_tecnico_no_puede_acceder_a_auditoria(): void
    {
        $usuario = $this->crearUsuario('tecnico');

        $this->actingAs($usuario)
            ->get(route('auditoria.index'))
            ->assertForbidden();
    }

    public function test_administrador_puede_acceder_a_auditoria(): void
    {
        $usuario = $this->crearUsuario('administrador');

        $this->actingAs($usuario)
            ->get(route('auditoria.index'))
            ->assertOk()
            ->assertSee('Auditoría del sistema');
    }

    // PERMISOS DE REPORTES

    public function test_cotizador_no_puede_acceder_a_reportes_de_inventario(): void
    {
        $usuario = $this->crearUsuario('cotizador');

        $this->actingAs($usuario)
            ->get(route('inventario.reportes.stock'))
            ->assertForbidden();
    }

    public function test_tecnico_no_puede_acceder_a_reportes_de_inventario(): void
    {
        $usuario = $this->crearUsuario('tecnico');

        $this->actingAs($usuario)
            ->get(route('inventario.reportes.stock'))
            ->assertForbidden();
    }

    public function test_administrador_puede_acceder_a_reportes_de_inventario(): void
    {
        $usuario = $this->crearUsuario('administrador');

        $this->actingAs($usuario)
            ->get(route('inventario.reportes.stock'))
            ->assertOk();
    }

    // PERMISOS DE ADMINISTRACIÓN DE USUARIOS

    public function test_cotizador_no_puede_administrar_usuarios(): void
    {
        $usuario = $this->crearUsuario('cotizador');

        $this->actingAs($usuario)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    public function test_tecnico_no_puede_administrar_usuarios(): void
    {
        $usuario = $this->crearUsuario('tecnico');

        $this->actingAs($usuario)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    public function test_administrador_puede_administrar_usuarios(): void
    {
        $usuario = $this->crearUsuario('administrador');

        $this->actingAs($usuario)
            ->get(route('usuarios.index'))
            ->assertOk();
    }
}
