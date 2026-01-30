<?php

namespace App\Traits;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply filters to the query
     */
    public function scopeFilter(Builder $query, BaseFilter $filter): Builder
    {
        return $filter->apply($query);
    }
}