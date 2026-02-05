<?php

namespace App\Filters\Vendor;

use App\Filters\BaseFilter;

class ProductFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'is_active',
        'shop_id',
        'subcategory_id',
        'category_id',
        'min_price',
        'max_price',
        'in_stock',
        'on_discount',
        'attributes',
        'city_id',
        'area',
        'near',
        'sort',
    ];

    protected array $sortable = [
        'name',
        'price',
        'created_at',
    ];

    /**
     * Search by product name and description
     */
    public function search($term): void
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Filter by active status
     */
    public function is_active($status = true): void
    {
        $this->builder->where('is_active', $status);
    }

    /**
     * Filter by shop ID
     */
    public function shop_id($shopId): void
    {
        $this->builder->where('shop_id', $shopId);
    }

    /**
     * Filter by subcategory ID
     */
    public function subcategory_id($subcategoryId): void
    {
        $this->builder->where('subcategory_id', $subcategoryId);
    }

    /**
     * Filter by category ID (through subcategory)
     */
    public function category_id($categoryId): void
    {
        $this->builder->whereHas('subcategory', function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        });
    }

    /**
     * Filter by minimum price
     */
    public function min_price($minPrice): void
    {
        $this->builder->where('price', '>=', $minPrice);
    }

    /**
     * Filter by maximum price
     */
    public function max_price($maxPrice): void
    {
        $this->builder->where('price', '<=', $maxPrice);
    }

    /**
     * Filter by stock availability
     */
    public function in_stock($inStock = true): void
    {
        if ($inStock) {
            $this->builder->where('stock_quantity', '>', 0);
        } else {
            $this->builder->where('stock_quantity', '<=', 0);
        }
    }

    /**
     * Filter by discount availability
     */
    public function on_discount($onDiscount = true): void
    {
        if ($onDiscount) {
            $this->builder->whereNotNull('discount_type')
                ->whereNotNull('discount_value');
        } else {
            $this->builder->where(function ($query) {
                $query->whereNull('discount_type')
                    ->orWhereNull('discount_value');
            });
        }
    }

    /**
     * Filter by attribute values
     * Expected format: attributes[attribute_slug][] = value_slug
     * Example: attributes[color][] = red&attributes[color][] = blue&attributes[size][] = large
     */
    public function attributes($attributeFilters): void
    {
        if (!is_array($attributeFilters)) {
            return;
        }

        foreach ($attributeFilters as $attributeSlug => $valueFilters) {
            if (!is_array($valueFilters) || empty($valueFilters)) {
                continue;
            }

            // Filter products that have ANY of the specified values for this attribute
            $this->builder->whereHas('attributeValues', function ($query) use ($attributeSlug, $valueFilters) {
                $query->whereHas('attribute', function ($q) use ($attributeSlug) {
                    $q->where('slug', $attributeSlug);
                })->whereIn('slug', $valueFilters);
            });
        }
    }

    /**
     * Filter by city (through shop location)
     */
    public function city_id($cityId): void
    {
        $this->builder->whereHas('shop.location', function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        });
    }

    /**
     * Filter by area (through shop location)
     */
    public function area($area): void
    {
        $this->builder->whereHas('shop.location', function ($query) use ($area) {
            $query->where('area', 'LIKE', "%{$area}%");
        });
    }

    /**
     * Filter by proximity to coordinates
     */
    public function near($coordinates): void
    {
        if (!is_array($coordinates) || !isset($coordinates['lat']) || !isset($coordinates['lng'])) {
            return;
        }

        $lat = $coordinates['lat'];
        $lng = $coordinates['lng'];
        $radius = $coordinates['radius'] ?? 10; // Default 10km

        $this->builder->whereHas('shop.location', function ($query) use ($lat, $lng, $radius) {
            $query->selectRaw("
                *, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) + sin(radians(?)) *
                sin(radians(latitude)))) AS distance
            ", [$lat, $lng, $lat])
                ->havingRaw('distance <= ?', [$radius])
                ->orderBy('distance');
        });
    }

    /**
     * Sort by specified field with direction
     * Format: 'field' or '-field' (for desc)
     */
    public function sort($value): void
    {
        $value = trim($value);

        $direction = str_starts_with($value, '-') ? 'desc' : 'asc';
        $column = ltrim($value, '-');

        if (!in_array($column, $this->sortable, true)) {
            // Default sorting for invalid fields
            $this->builder->orderByDesc('created_at');
            return;
        }

        $this->builder->orderBy($column, $direction);
    }
}
