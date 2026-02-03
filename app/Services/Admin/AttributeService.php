<?php

namespace App\Services\Admin;

use App\Filters\Admin\AttributeFilter;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AttributeService
{
    /**
     * Get attributes with filtering and pagination.
     */
    public function getAttributes(Request $request): Collection
    {
        return Attribute::with(['attributeValues'])
            ->filter(new AttributeFilter($request))
            ->get();
    }

    /**
     * Get a single attribute with relationships.
     */
    public function getAttribute(Attribute $attribute): Attribute
    {
        return $attribute->load(['attributeValues']);
    }

    /**
     * Create a new attribute.
     */
    public function createAttribute(array $data): Attribute
    {
        $attribute = Attribute::create($data);
        
        return $attribute->load(['attributeValues']);
    }

    /**
     * Update an attribute.
     */
    public function updateAttribute(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute->refresh()->load(['attributeValues']);
    }

    /**
     * Delete an attribute.
     */
    public function deleteAttribute(Attribute $attribute): bool
    {
        return $attribute->delete();
    }
}