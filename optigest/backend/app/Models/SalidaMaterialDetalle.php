<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalidaMaterialDetalle extends Model
{
    protected $table = 'salida_material_detalle';

    protected $fillable = ['salida_id', 'material_id', 'descripcion', 'cantidad', 'valor_unitario', 'total'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function salida()
    {
        return $this->belongsTo(SalidaMaterial::class, 'salida_id');
    }
}