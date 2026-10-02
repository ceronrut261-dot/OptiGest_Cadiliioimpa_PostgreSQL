<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoServicio extends Model
{
    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'categoria', 'unidad',
        'precio_estandar', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_estandar' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::orderByDesc('id')->first();
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;

        return 'SRV-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}
