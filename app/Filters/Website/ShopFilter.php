<?php

namespace App\Filters\Website;

use App\Filters\BaseFilter;

class ShopFilter extends BaseFilter
{
     protected array $allowed = [
        'city_id',
        'area',
        'search',
        'sort',
    ];
    /**
     * Sortable fields for public shop browsing
     */
    protected array $sortable = [
        'name',
    ];

    /**
     * Filter by city ID
     */
    public function city_id($cityId): void
    {
        $this->builder->whereHas('location.city', function ($query) use ($cityId) {
            $query->where('id', $cityId);
        });
    }

    /**
     * Filter by area (partial match)
     */
    public function area($area): void
    {
        $this->builder->whereHas('location', function ($query) use ($area) {
            $query->where('area', 'LIKE', "%{$area}%");
        });
    }

    /**
     * Search by shop name or description
     */
    public function search($term): void
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
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