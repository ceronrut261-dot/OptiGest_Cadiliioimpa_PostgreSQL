<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Equivalente a database/migracion_mejoras_v2.sql. Cada paso revisa si ya
 * existe, así que funciona tanto si aplicaste el SQL a mano como si no.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalogo_servicios')) {
            Schema::create('catalogo_servicios', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 20)->unique();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->string('categoria', 100)->nullable();
                $table->string('unidad', 30)->default('servicio');
                $table->decimal('precio_estandar', 10, 2)->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket_tecnicos')) {
            Schema::create('ticket_tecnicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('rol', 15)->default('apoyo');
                $table->decimal('monto_trato', 10, 2)->default(0);
                $table->timestamps();
                $table->unique(['ticket_id', 'user_id']);
            });

            DB::statement("INSERT INTO ticket_tecnicos (ticket_id, user_id, rol, monto_trato, created_at, updated_at)
                SELECT id, tecnico_id, 'responsable', 0, NOW(), NOW() FROM tickets WHERE tecnico_id IS NOT NULL
                ON CONFLICT (ticket_id, user_id) DO NOTHING");
        }

        if (! Schema::hasTable('cotizacion_servicios')) {
            Schema::create('cotizacion_servicios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
                $table->foreignId('servicio_id')->nullable()->constrained('catalogo_servicios')->nullOnDelete();
                $table->string('descripcion');
                $table->decimal('cantidad', 8, 2)->default(1);
                $table->decimal('precio_unitario', 10, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket_gastos')) {
            Schema::create('ticket_gastos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
                $table->foreignId('salida_id')->nullable()->constrained('salidas_materiales')->nullOnDelete();
                $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->nullOnDelete();
                $table->string('descripcion');
                $table->string('comercio')->nullable();
                $table->string('tipo_documento', 20)->default('factura');
                $table->string('numero_documento', 60)->nullable();
                $table->decimal('monto', 12, 2);
                $table->date('fecha_gasto');
                $table->boolean('cobrar_al_cliente')->default(true);
                $table->string('archivo_path', 500)->nullable();
                $table->string('archivo_nombre')->nullable();
                $table->string('archivo_mime', 100)->nullable();
                $table->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
                $table->timestamps();
                $table->index('ticket_id');
            });
        }

        Schema::table('cotizaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('cotizaciones', 'mano_obra_total')) {
                $table->decimal('mano_obra_total', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('cotizaciones', 'gastos_adicionales_total')) {
                $table->decimal('gastos_adicionales_total', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('cotizaciones', 'iva_aplicado')) {
                $table->boolean('iva_aplicado')->default(false);
            }
            if (! Schema::hasColumn('cotizaciones', 'iva_monto')) {
                $table->decimal('iva_monto', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['mano_obra_total', 'gastos_adicionales_total', 'iva_aplicado', 'iva_monto']);
        });
        Schema::dropIfExists('ticket_gastos');
        Schema::dropIfExists('cotizacion_servicios');
        Schema::dropIfExists('ticket_tecnicos');
        Schema::dropIfExists('catalogo_servicios');
    }
};
