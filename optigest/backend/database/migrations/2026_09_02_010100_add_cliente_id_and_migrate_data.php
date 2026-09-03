<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Antes, "cliente" era solo un campo de texto libre repetido en tickets y
 * cotizaciones. Esta migración:
 *  1. Agrega cliente_id (nullable primero) a tickets y cotizaciones.
 *  2. Recorre los tickets/cotizaciones existentes, crea un registro en
 *     "clientes" por cada nombre distinto (reutilizando teléfono/dirección
 *     si el ticket los tenía), y enlaza cliente_id.
 *  3. Vuelve cliente_id obligatorio y elimina las columnas de texto viejas
 *     (cliente, telefono_cliente, direccion), que ya quedaron redundantes.
 *
 * No se pierde ningún dato: todo lo que estaba en las columnas de texto
 * termina en la tabla "clientes".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->restrictOnDelete();
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->restrictOnDelete();
        });

        // --- Migrar datos existentes ---
        // nombre de cliente (normalizado en minúsculas y sin espacios extra) => id en "clientes"
        $clientesCreados = [];

        $resolverClienteId = function (?string $nombre, ?string $telefono, ?string $direccion) use (&$clientesCreados) {
            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                $nombre = 'Cliente sin nombre';
            }
            $clave = mb_strtolower($nombre);

            if (isset($clientesCreados[$clave])) {
                // Si ya existe y nos falta teléfono/dirección, completamos con lo que tengamos.
                $id = $clientesCreados[$clave];
                if ($telefono || $direccion) {
                    DB::table('clientes')->where('id', $id)->update(array_filter([
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                    ]));
                }

                return $id;
            }

            $id = DB::table('clientes')->insertGetId([
                'nombre' => $nombre,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $clientesCreados[$clave] = $id;

            return $id;
        };

        foreach (DB::table('tickets')->orderBy('id')->get() as $ticket) {
            $clienteId = $resolverClienteId($ticket->cliente, $ticket->telefono_cliente, $ticket->direccion);
            DB::table('tickets')->where('id', $ticket->id)->update(['cliente_id' => $clienteId]);
        }

        foreach (DB::table('cotizaciones')->orderBy('id')->get() as $cotizacion) {
            if ($cotizacion->ticket_id && $clienteIdDelTicket = DB::table('tickets')->where('id', $cotizacion->ticket_id)->value('cliente_id')) {
                $clienteId = $clienteIdDelTicket;
            } else {
                $clienteId = $resolverClienteId($cotizacion->cliente, null, null);
            }
            DB::table('cotizaciones')->where('id', $cotizacion->id)->update(['cliente_id' => $clienteId]);
        }

        // --- Ya migrados los datos: columnas obligatorias y limpieza ---
        // Se usa SQL directo (en vez de ->change()) porque ->change() requiere
        // el paquete doctrine/dbal, que este proyecto no tiene instalado.
        DB::statement('ALTER TABLE tickets ALTER COLUMN cliente_id SET NOT NULL');
        DB::statement('ALTER TABLE cotizaciones ALTER COLUMN cliente_id SET NOT NULL');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['cliente', 'telefono_cliente', 'direccion']);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('cliente');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('cliente')->nullable();
            $table->string('telefono_cliente', 20)->nullable();
            $table->string('direccion')->nullable();
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('cliente')->nullable();
        });

        foreach (DB::table('tickets')->orderBy('id')->get() as $ticket) {
            $cliente = DB::table('clientes')->find($ticket->cliente_id);
            DB::table('tickets')->where('id', $ticket->id)->update([
                'cliente' => $cliente->nombre ?? '',
                'telefono_cliente' => $cliente->telefono ?? null,
                'direccion' => $cliente->direccion ?? null,
            ]);
        }

        foreach (DB::table('cotizaciones')->orderBy('id')->get() as $cotizacion) {
            $cliente = DB::table('clientes')->find($cotizacion->cliente_id);
            DB::table('cotizaciones')->where('id', $cotizacion->id)->update([
                'cliente' => $cliente->nombre ?? '',
            ]);
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
        });
    }
};
