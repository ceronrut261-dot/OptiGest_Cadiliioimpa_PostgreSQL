<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;

        return 'TKT-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}
