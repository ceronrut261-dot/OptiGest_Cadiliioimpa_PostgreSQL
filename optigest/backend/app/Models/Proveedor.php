<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    // "nombre" = nombre del proveedor/empresa (lo que se muestra en
    // cotizaciones, comparación de precios, etc. en todo el sistema).
    // "nombre_contacto" = la persona de contacto dentro de ese proveedor.
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
}