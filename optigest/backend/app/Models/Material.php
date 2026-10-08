<?php 
 
namespace App\Models; 
 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use App\Observers\MaterialObserver; 
use App\Models\PrecioProveedorMaterial; 
 
class Material extends Model 
{ 
    use HasFactory; 
 
    protected $table = 'materiales'; 
 
    protected $fillable = [ 
        'codigo', 'nombre', 'categoria', 'descripcion', 'precio', 'stock', 
        'stock_minimo', 'unidad_medida', 'proveedor_id', 'activo', 'en_bodega',
    ]; 
 
    protected function casts(): array 
    { 
        return [ 
            'precio' => 'decimal:2', 
            'stock' => 'integer', 
            'stock_minimo' => 'integer', 
            'activo' => 'boolean', 
            'en_bodega' => 'boolean',
        ]; 
    } 
 
    public function proveedor() 
    { 
        return $this->belongsTo(Proveedor::class); 
    } 

    public function preciosProveedor()
    {
        return $this->hasMany(PrecioProveedorMaterial::class, 'material_id');
    }

    /**
     * Compara todos los proveedores disponibles para este material.
     * Devuelve el más barato y los competidores con sus precios.
     */
    public function comparativaProveedores(): ?array
    {
        $candidatos = collect();

        // 1. Proveedor asignado en la ficha del material
        if ($this->proveedor_id && $this->precio > 0 && $this->proveedor) {
            $candidatos->push([
                'proveedor' => $this->proveedor->nombre,
                'precio' => (float) $this->precio,
            ]);
        }

        // 2. Precios adicionales registrados en precios_proveedor_material
        foreach ($this->preciosProveedor as $registro) {
            if ($registro->proveedor && (float) $registro->precio > 0) {
                $candidatos->push([
                    'proveedor' => $registro->proveedor->nombre,
                    'precio' => (float) $registro->precio,
                ]);
            }
        }

        $unicos = $candidatos->unique(fn ($item) => $item['proveedor'].'-'.$item['precio'])->sortBy('precio')->values();

        if ($unicos->isEmpty()) {
            return null;
        }

        return [
            'mas_barato' => $unicos->first(),
            'otros' => $unicos->slice(1)->values()->all(),
            'total_opciones' => $unicos->count(),
        ];
    }

    public function mejorProveedor(): ?array
    {
        $comp = $this->comparativaProveedores();
        return $comp ? $comp['mas_barato'] : null;
    }
 
    public function historialPrecios() 
    { 
        return $this->hasMany(HistorialPrecioMaterial::class, 'material_id'); 
    } 
 
    protected static function booted(): void 
    { 
        static::observe(MaterialObserver::class); 
    } 
 
    public function movimientos() 
    { 
        return $this->hasMany(MovimientoInventario::class); 
    } 
 
    public function detallesCotizacion() 
    { 
        return $this->hasMany(CotizacionDetalle::class); 
    } 
 
    public function getBajoStockAttribute(): bool 
    { 
        if (! $this->en_bodega) {
            return false;
        }
        return $this->stock <= $this->stock_minimo; 
    } 
 
    public function scopeBajoStock($query) 
    { 
        return $query->where('en_bodega', true)->whereColumn('stock', '<=', 'stock_minimo'); 
    } 

    public function scopeEnBodega($query)
    {
        return $query->where('en_bodega', true);
    }

    public function scopeSoloCotizable($query)
    {
        return $query->where('en_bodega', false);
    }
 
    public static function generarCodigo(): string 
    { 
        $ultimo = static::orderByDesc('id')->first(); 
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1; 
 
        return 'MAT-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT); 
    } 
}