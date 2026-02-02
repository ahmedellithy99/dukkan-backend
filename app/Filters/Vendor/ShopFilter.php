<?php

namespace App\Filters\Vendor;

use App\Filters\BaseFilter;

class ShopFilter extends BaseFilter
{
    protected array $allowed = [
        'is_active',
    ];

    /**
     * Filter by active status
     */
    public function is_active($status)
    {
        $this->builder->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
    }
}