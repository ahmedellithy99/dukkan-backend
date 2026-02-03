<?php

namespace App\Filters\Admin;

use App\Filters\BaseFilter;

class AttributeFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'sort',
    ];

    protected array $sortable = [
        'name',
    ];

    /**
     * Search by attribute name
     */
    public function search($term): void
    {
        $this->builder->where('name', 'LIKE', "%{$term}%");
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
            $this->builder->orderBy('name', 'asc');
            return;
        }

        $this->builder->orderBy($column, $direction);
    }
}