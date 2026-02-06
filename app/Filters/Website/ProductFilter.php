<?php

namespace App\Filters\Website;

use App\Filters\BaseFilter;

class ProductFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
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

    //GET /api/v1/products?av[color]=12,13&av[size]=44,45&av[brand]=88
    public function attributes($filters): void
    {
        if (!is_array($filters) || empty($filters)) return;

        foreach ($filters as $attributeSlug => $csvIds) {
            $ids = is_array($csvIds)
                ? array_map('intval', $csvIds)
                : array_filter(array_map('intval', explode(',', (string) $csvIds)));

            if (!$ids) continue;

            $this->builder->whereExists(function ($sub) use ($ids) {
                $sub->selectRaw(1)
                    ->from('product_attribute_values as pav')
                    ->whereColumn('pav.product_id', 'products.id')
                    ->whereIn('pav.attribute_value_id', $ids);
            });
        }
    }

    /**
     * Filter by city (through shop location)
     */
    public function city_id($cityId)
    {
        $this->builder->whereHas('shop.location', function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        });
    }

    /**
     * Filter by area (through shop location)
     */
    public function area($area)
    {
        $this->builder->whereHas('shop.location', function ($query) use ($area) {
            $query->where('area', 'LIKE', "%{$area}%");
        });
    }

    /**
     * Filter by proximity to coordinates
     */
    public function near($coordinates)
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
