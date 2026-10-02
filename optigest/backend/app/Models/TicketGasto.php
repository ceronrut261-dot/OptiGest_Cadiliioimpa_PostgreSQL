<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketGasto extends Model
{
    protected $table = 'ticket_gastos';

    const TIPOS_DOCUMENTO = [
        'factura' => 'Factura',
        'recibo' => 'Recibo',
        'ticket_caja' => 'Ticket de caja',
        'otro' => 'Otro documento',
        'sin_comprobante' => 'Sin comprobante (pendiente)',
    ];

    protected $fillable = [
        'ticket_id', 'salida_id', 'cotizacion_id', 'descripcion', 'comercio',
        'tipo_documento', 'numero_documento', 'monto', 'fecha_gasto',
        'cobrar_al_cliente', 'archivo_path', 'archivo_nombre', 'archivo_mime',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_gasto' => 'date',
            'cobrar_al_cliente' => 'boolean',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function salida()
    {
        return $this->belongsTo(SalidaMaterial::class, 'salida_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getTieneArchivoAttribute(): bool
    {
        return ! empty($this->archivo_path);
    }

    public function getEsImagenAttribute(): bool
    {
        return str_starts_with((string) $this->archivo_mime, 'image/');
    }
}
