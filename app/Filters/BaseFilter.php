<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Request $request;
    protected Builder $builder;

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
        return $this->request->all();
    }

    protected function hasValue($value): bool
    {
        return $value !== null && $value !== '' && $value !== [];
    }
}