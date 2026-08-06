<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    const TIPO_ENTRADA = 'entrada';
    const TIPO_SALIDA = 'salida';

    protected $fillable = [
        'material_id', 'tipo', 'cantidad', 'motivo', 'fecha',
        'usuario_id', 'cotizacion_id', 'stock_resultante',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'cantidad' => 'integer',
            'stock_resultante' => 'integer',
        ];
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }
}
