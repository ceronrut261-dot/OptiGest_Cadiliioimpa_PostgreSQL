<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('accion', 50);
            $table->string('modulo', 100);
            $table->string('registro_tipo', 150)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('descripcion')->nullable();

            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['modulo', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['registro_tipo', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_logs');
    }
};
