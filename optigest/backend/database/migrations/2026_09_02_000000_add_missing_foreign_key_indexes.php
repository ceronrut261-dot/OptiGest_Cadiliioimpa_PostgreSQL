<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL, a diferencia de MySQL, no crea automáticamente un índice
 * sobre la columna que referencia una llave foránea. Esta migración
 * agrega esos índices donde faltaban, para que los listados y filtros
 * por técnico, cotizador, ticket, material y proveedor no requieran un
 * recorrido secuencial de la tabla a medida que crezcan los datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('tecnico_id');
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->index('ticket_id');
            $table->index('cotizador_id');
        });

        Schema::table('cotizacion_detalle', function (Blueprint $table) {
            $table->index('material_id');
        });

        Schema::table('materiales', function (Blueprint $table) {
            $table->index('proveedor_id');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index('usuario_id');
            $table->index('cotizacion_id');
        });

        Schema::table('conversaciones_ia', function (Blueprint $table) {
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['tecnico_id']);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
            $table->dropIndex(['cotizador_id']);
        });

        Schema::table('cotizacion_detalle', function (Blueprint $table) {
            $table->dropIndex(['material_id']);
        });

        Schema::table('materiales', function (Blueprint $table) {
            $table->dropIndex(['proveedor_id']);
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex(['usuario_id']);
            $table->dropIndex(['cotizacion_id']);
        });

        Schema::table('conversaciones_ia', function (Blueprint $table) {
            $table->dropIndex(['usuario_id']);
        });
    }
};
