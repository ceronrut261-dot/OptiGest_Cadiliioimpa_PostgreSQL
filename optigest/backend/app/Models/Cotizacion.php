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
        'codigo', 'cliente', 'ticket_id', 'cotizador_id', 'estado',
        'subtotal', 'total', 'fecha', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
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

    public function recalcularTotales(): void
    {
        $subtotal = $this->detalles()->sum('subtotal');

        $this->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }
}
