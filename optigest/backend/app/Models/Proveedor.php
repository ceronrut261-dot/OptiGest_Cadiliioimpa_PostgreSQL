<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre', 'nombre_contacto', 'telefono', 'descripcion',
        'nit', 'contacto', 'email', 'direccion', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function materiales()
    {
        return $this->hasMany(Material::class);
    }

    public function preciosProveedor()
    {
        return $this->hasMany(PrecioProveedorMaterial::class, 'proveedor_id');
    }
}