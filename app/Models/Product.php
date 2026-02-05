<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasSlug, Filterable;

    protected $fillable = [
        'shop_id',
        'subcategory_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_type',
        'discount_value',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(50)
            ->usingSeparator('-')
            ->extraScope(fn ($builder) => $builder->where('shop_id', $this->shop_id));
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values');
    }

    public function stats()
    {
        return $this->hasOne(ProductStats::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeOnDiscount($query)
    {
        return $query->whereNotNull('discount_type')
                    ->whereNotNull('discount_value');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')->singleFile()->useDisk('public');
        $this->addMediaCollection('secondary_image')->singleFile()->useDisk('public');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->format('webp')
            ->quality(80)
            ->performOnCollections('main_image', 'secondary_image')
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1400, 1400)
            ->format('webp')
            ->quality(82)
            ->performOnCollections('main_image', 'secondary_image')
            ->nonQueued();
    }

    // Stock helper methods
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock_quantity < $quantity) {
            return false; // Insufficient stock
        }

        $this->decrement('stock_quantity', $quantity);
        return true;
    }

    public function incrementStock(int $quantity = 1): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    // Discount helper methods
    public function hasDiscount(): bool
    {
        return $this->discount_type !== null && $this->discount_value !== null;
    }

    public function getDiscountedPrice(): ?float
    {
        if (!$this->price || !$this->hasDiscount()) {
            return $this->price;
        }

        return match($this->discount_type) {
            'percent' => round($this->price * (1 - ($this->discount_value / 100)), 2),
            'amount' => round(max(0, $this->price - $this->discount_value), 2),
            default => $this->price
        };
    }

    public function getSavingsAmount(): ?float
    {
        if (!$this->hasDiscount() || !$this->price) {
            return null;
        }

        return round($this->price - $this->getDiscountedPrice(), 2);
    }
}
