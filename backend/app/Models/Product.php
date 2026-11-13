<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'type', 'sku', 'brand', 'unit', 'category_id', 'color', 'tags', 'price', 'old_price',
    'cost', 'tax_rate', 'min_order_qty', 'image', 'rating', 'stock', 'badge', 'badge_class',
    'description', 'bot_description', 'warranty', 'specs', 'images', 'is_featured', 'is_active',
    'is_shareable_on_whatsapp', 'show_to_bot',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'min_order_qty' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_shareable_on_whatsapp' => 'boolean',
            'show_to_bot' => 'boolean',
            'specs' => 'array',
            'images' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function saleItemsThroughSales(): Builder
    {
        return SaleItem::query()->where('product_id', $this->id);
    }

    public function imageUrl(): string
    {
        if (blank($this->image)) {
            return asset('assets/images/product/img-1.png');
        }

        return str_starts_with((string) $this->image, 'http') || str_starts_with((string) $this->image, 'assets/')
            ? asset($this->image)
            : asset('storage/'.$this->image);
    }

    public function primaryImageUrl(): string
    {
        $media = $this->relationLoaded('media') ? $this->media : $this->media()->get();
        $image = $media->where('type', 'image')->first();

        return $image?->url() ?? $this->imageUrl();
    }

    public function margin(): float
    {
        $price = (float) $this->price;

        if ($price <= 0) {
            return 0.0;
        }

        return round((($price - (float) $this->cost) / $price) * 100, 1);
    }
}
