<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasOpaqueRouteKey;

class SalidaMaterial extends Model
{
    use HasOpaqueRouteKey;

    protected $table = 'salidas_materiales';

    protected $fillable = [
        'codigo',
        'usuario_id',
        'proyecto',
        'persona_recibe',
        'ticket_id',
        'fecha',
        'observaciones',
        'falta_comprar',
        'total',
        'estado_entrega',
        'confirmado_por',
        'confirmado_en',
        'observaciones_entrega',
        'salida_original_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'confirmado_en' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function usuarioConfirmacion()
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function detalles()
    {
        return $this->hasMany(SalidaMaterialDetalle::class, 'salida_id');
    }

    // Vale original del que depende esta salida complementaria.
    public function salidaOriginal()
    {
        return $this->belongsTo(SalidaMaterial::class, 'salida_original_id');
    }

    // Vales complementarios generados a partir de este vale.
    public function salidasComplementarias()
    {
        return $this->hasMany(SalidaMaterial::class, 'salida_original_id');
    }

    public function recalcularTotal(): void
    {
        $this->update([
            'total' => $this->detalles()->sum('total'),
        ]);
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();

        $siguiente = $ultimo
            ? ((int) substr($ultimo->codigo, 4)) + 1
            : 1;

        return 'SAL-' . str_pad(
            (string) $siguiente,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}
