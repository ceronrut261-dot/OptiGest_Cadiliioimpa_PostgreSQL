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
        'stock_minimo', 'unidad_medida', 'proveedor_id', 'activo', 
    ]; 
 
    protected function casts(): array 
    { 
        return [ 
            'precio' => 'decimal:2', 
            'stock' => 'integer', 
            'stock_minimo' => 'integer', 
            'activo' => 'boolean', 
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
     * Compara el precio de catálogo (proveedor asignado en la ficha del
     * material) contra todos los precios registrados en
     * precios_proveedor_material, y devuelve el más barato.
     * Devuelve null si no hay ningún proveedor con precio conocido.
     */
    public function mejorProveedor(): ?array
    {
        $candidatos = collect();

        if ($this->proveedor_id && $this->precio > 0) {
            $candidatos->push([
                'proveedor' => $this->proveedor?->nombre,
                'precio' => (float) $this->precio,
            ]);
        }

        foreach ($this->preciosProveedor()->with('proveedor')->get() as $registro) {
            $candidatos->push([
                'proveedor' => $registro->proveedor->nombre,
                'precio' => (float) $registro->precio,
            ]);
        }

        return $candidatos->sortBy('precio')->first();
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
        return $this->stock <= $this->stock_minimo; 
    } 
 
    public function scopeBajoStock($query) 
    { 
        return $query->whereColumn('stock', '<=', 'stock_minimo'); 
    } 
 
    public static function generarCodigo(): string 
    { 
        $ultimo = static::orderByDesc('id')->first(); 
        $siguiente = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1; 
 
        return 'MAT-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT); 
    } 
}

