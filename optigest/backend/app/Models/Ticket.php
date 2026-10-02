<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    const ESTADOS = ['pendiente', 'asignado', 'en_proceso', 'completado', 'cancelado'];

    protected $fillable = [
        'codigo', 'cliente_id', 'descripcion',
        'prioridad', 'estado', 'tecnico_id', 'fecha_programada', 'fecha_completado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'datetime',
            'fecha_completado' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    /**
     * Equipo completo del ticket (responsable + técnicos de apoyo).
     * monto_trato es el pago pactado por trato con cada técnico.
     */
    public function tecnicos()
    {
        return $this->belongsToMany(User::class, 'ticket_tecnicos', 'ticket_id', 'user_id')
            ->withPivot(['rol', 'monto_trato'])
            ->withTimestamps();
    }

    public function gastos()
    {
        return $this->hasMany(TicketGasto::class);
    }

    public function salidas()
    {
        return $this->hasMany(SalidaMaterial::class, 'ticket_id');
    }

    /**
     * Recalcula las cotizaciones que todavía se pueden modificar
     * (borrador/enviada) para que incluyan los gastos cobrables nuevos.
     */
    public function sincronizarCotizacionesAbiertas(): void
    {
        $this->cotizaciones()
            ->whereIn('estado', ['borrador', 'enviada'])
            ->get()
            ->each->recalcularTotales();
    }

    /**
     * Resumen para ver el flujo de caja del ticket: qué se cobra, qué se
     * gasta y cuánto queda. Es una estimación; el IVA no cuenta como ingreso.
     */
    public function resumenFinanciero(): array
    {
        $aprobadas = $this->cotizaciones()->where('estado', 'aprobada')->get();

        $ingreso = (float) $aprobadas->sum('total') - (float) $aprobadas->sum('iva_monto');
        $gastosTotal = (float) $this->gastos()->sum('monto');
        $gastosCobrables = (float) $this->gastos()->where('cobrar_al_cliente', true)->sum('monto');
        $gastosPendientes = (float) $this->gastos()
            ->where('cobrar_al_cliente', true)
            ->whereNull('cotizacion_id')
            ->sum('monto');
        $gastosSinComprobante = $this->gastos()
            ->whereNull('archivo_path')
            ->count();
        $pagoTecnicos = (float) DB::table('ticket_tecnicos')
            ->where('ticket_id', $this->id)
            ->sum('monto_trato');
        $materialesBodega = (float) $this->salidas()->sum('total');

        $costo = $gastosTotal + $pagoTecnicos + $materialesBodega;

        return [
            'ingreso_aprobado' => $ingreso,
            'gastos_total' => $gastosTotal,
            'gastos_cobrables' => $gastosCobrables,
            'gastos_pendientes_cobro' => $gastosPendientes,
            'gastos_sin_comprobante' => $gastosSinComprobante,
            'pago_tecnicos' => $pagoTecnicos,
            'materiales_bodega' => $materialesBodega,
            'costo_total' => $costo,
            'utilidad' => $ingreso - $costo,
            'hay_cotizacion_aprobada' => $aprobadas->isNotEmpty(),
        ];
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;

        return 'TKT-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}
