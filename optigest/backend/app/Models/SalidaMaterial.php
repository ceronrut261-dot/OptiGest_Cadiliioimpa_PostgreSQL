<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalidaMaterial extends Model
{
    protected $table = 'salidas_materiales';

    protected $fillable = [
        'codigo', 'usuario_id', 'proyecto', 'persona_recibe', 'ticket_id',
        'fecha', 'observaciones', 'falta_comprar', 'total',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function detalles()
    {
        return $this->hasMany(SalidaMaterialDetalle::class, 'salida_id');
    }

    public function recalcularTotal(): void
    {
        $this->update(['total' => $this->detalles()->sum('total')]);
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;

        return 'SAL-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}