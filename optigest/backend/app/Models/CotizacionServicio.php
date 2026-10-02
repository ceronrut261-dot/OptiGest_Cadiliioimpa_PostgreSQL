<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionServicio extends Model
{
    protected $table = 'cotizacion_servicios';

    protected $fillable = [
        'cotizacion_id', 'servicio_id', 'descripcion', 'cantidad',
        'precio_unitario', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function servicio()
    {
        return $this->belongsTo(CatalogoServicio::class, 'servicio_id');
    }
}
