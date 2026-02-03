<?php

namespace App\Filters\Admin;

use App\Filters\BaseFilter;

class AttributeValueFilter extends BaseFilter
{
    protected array $allowed = [
        'attribute_name',
        'search',
    ];

    protected array $sortable = [
        'value',
    ];

    /**
     * Filter by attribute ID
     */
    public function attribute_name($attributeName): void
    {
        $this->builder->whereHas('attribute', fn($q) => $q->where('name', $attributeName));
    }

    /**
     * Search by attribute value or attribute name
     */
    public function search($term): void
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('value', 'LIKE', "%{$term}%")
                ->orWhereHas('attribute', function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%");
                });
        });
    }
}
