<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
        'verial_id',
        'barcode',
        'tipo_articulo',
        'iva_percent',
        'fecha_disponibilidad',
        'fecha_inicio_venta',
        'fecha_inactivo',
        'nexo',
        'peso',
        'verial_fabricante_id',
        'verial_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'price'                => 'decimal:2',
            'stock'                => 'integer',
            'is_active'            => 'boolean',
            'tipo_articulo'        => 'integer',
            'iva_percent'          => 'decimal:2',
            'fecha_disponibilidad' => 'date',
            'fecha_inicio_venta'   => 'date',
            'fecha_inactivo'       => 'datetime',
            'peso'                 => 'decimal:3',
            'verial_synced_at'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function formattedPrice(): string
    {
        return number_format((float) $this->price, 2, ',', '.') . ' €';
    }

    public function fabricante(): BelongsTo
    {
        return $this->belongsTo(VerialFabricante::class, 'verial_fabricante_id');
    }

    public function bookDetail(): HasOne
    {
        return $this->hasOne(ProductBookDetail::class);
    }

    public function isLibro(): bool
    {
        return (int) $this->tipo_articulo === 2;
    }

    public function isSyncedWithVerial(): bool
    {
        return $this->verial_id !== null;
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }
}
