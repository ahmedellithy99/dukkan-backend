<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Request $request;
    protected Builder $builder;
    
    // Whitelist of allowed filters
    protected array $allowed = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter) && $this->hasValue($value)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    protected function getFilters(): array
    {
        return $this->allowed
            ? $this->request->only($this->allowed)
            : $this->request->all();
    }

    protected function hasValue($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, fn($v) => $v !== null && $v !== '')) > 0;
        }
        
        return $value !== null && $value !== '';
    }
}