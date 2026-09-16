<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPrecioMaterial extends Model
{
    protected $table = 'historial_precios_materiales';

    protected $fillable = [
        'material_id', 'precio_anterior', 'precio_nuevo',
        'proveedor_id', 'usuario_id', 'motivo', 'fecha',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
