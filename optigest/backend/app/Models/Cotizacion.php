<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    // Se fija explicitamente el nombre de tabla porque Laravel pluraliza
    // "Cotizacion" incorrectamente como "Cotizacions" (no es ingles).
    protected $table = 'cotizaciones';

    const ESTADOS = ['borrador', 'enviada', 'aprobada', 'rechazada'];

    protected $fillable = [
        'codigo', 'cliente_id', 'ticket_id', 'cotizador_id', 'estado',
        'subtotal', 'total', 'fecha', 'observaciones',
        'mano_obra_total', 'gastos_adicionales_total', 'iva_aplicado', 'iva_monto',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'mano_obra_total' => 'decimal:2',
            'gastos_adicionales_total' => 'decimal:2',
            'iva_monto' => 'decimal:2',
            'iva_aplicado' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function cotizador()
    {
        return $this->belongsTo(User::class, 'cotizador_id');
    }

    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    public function servicios()
    {
        return $this->hasMany(CotizacionServicio::class);
    }

    public function gastos()
    {
        return $this->hasMany(TicketGasto::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;

        return 'COT-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Total = materiales + mano de obra estandarizada + gastos adicionales
     * cobrables del ticket (+ IVA si se marcó). Mientras la cotización esté
     * en borrador/enviada, "reclama" los gastos cobrables del ticket que aún
     * no se han cobrado en otra cotización. Al aprobarse queda congelada.
     */
    public function recalcularTotales(): void
    {
        $materiales = (float) $this->detalles()->sum('subtotal');
        $manoObra = (float) $this->servicios()->sum('subtotal');

        if ($this->ticket_id && in_array($this->estado, ['borrador', 'enviada'], true)) {
            TicketGasto::where('ticket_id', $this->ticket_id)
                ->where('cobrar_al_cliente', true)
                ->whereNull('cotizacion_id')
                ->update(['cotizacion_id' => $this->id]);
        }

        $gastos = (float) $this->gastos()->where('cobrar_al_cliente', true)->sum('monto');

        $subtotal = round($materiales + $manoObra + $gastos, 2);
        $iva = $this->iva_aplicado ? round($subtotal * config('optigest.iva'), 2) : 0;

        $this->update([
            'mano_obra_total' => $manoObra,
            'gastos_adicionales_total' => $gastos,
            'subtotal' => $subtotal,
            'iva_monto' => $iva,
            'total' => $subtotal + $iva,
        ]);
    }
}
