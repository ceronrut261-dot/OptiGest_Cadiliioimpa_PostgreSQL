<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecioProveedorMaterial extends Model
{
    protected $table = 'precios_proveedor_material';

    protected $fillable = ['material_id', 'proveedor_id', 'precio', 'actualizado_en'];

    protected function casts(): array
    {
        return ['actualizado_en' => 'datetime'];
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}