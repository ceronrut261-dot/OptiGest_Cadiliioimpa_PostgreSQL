<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('cliente');
            $table->string('telefono_cliente', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->text('descripcion');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('estado', ['pendiente', 'asignado', 'en_proceso', 'completado', 'cancelado'])
                ->default('pendiente')->index();
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_programada')->nullable();
            $table->dateTime('fecha_completado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
