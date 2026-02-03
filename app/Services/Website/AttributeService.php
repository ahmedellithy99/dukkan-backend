<?php

namespace App\Services\Website;

use App\Filters\Website\AttributeFilter;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AttributeService
{
    /**
     * Get all attributes with their values for filtering
     */
    public function getAttributes(Request $request): Collection
    {
        return Attribute::with(['attributeValues'])
            ->filter(new AttributeFilter($request))
            ->get();
    }

    /**
     * Get a single attribute with its values
     */
    public function getAttribute(Attribute $attribute): Attribute
    {
        return $attribute->load(['attributeValues']);
    }
}