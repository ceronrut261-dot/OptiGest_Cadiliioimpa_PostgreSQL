<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalidaMaterialDetalle extends Model
{
    protected $table = 'salida_material_detalle';

    protected $fillable = [
        'salida_id',
        'material_id',
        'descripcion',
        'cantidad',
        'cantidad_entregada',
        'valor_unitario',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'cantidad_entregada' => 'integer',
            'valor_unitario' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function salida()
    {
        return $this->belongsTo(
            SalidaMaterial::class,
            'salida_id'
        );
    }
}