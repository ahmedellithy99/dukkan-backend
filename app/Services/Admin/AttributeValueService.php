<?php

namespace App\Services\Admin;

use App\Filters\Admin\AttributeValueFilter;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AttributeValueService
{
    /**
     * Get attribute values with filtering and pagination.
     */
    public function getAttributeValues(Request $request): Collection
    {
        return AttributeValue::filter(new AttributeValueFilter($request))->get();
    }

    /**
     * Get a single attribute value with relationships.
     */
    public function getAttributeValue(AttributeValue $attributeValue): AttributeValue
    {
        return $attributeValue;
    }

    /**
     * Create a new attribute value.
     */
    public function createAttributeValue(array $data): AttributeValue
    {
        return AttributeValue::create($data);
    }

    /**
     * Update an attribute value.
     */
    public function updateAttributeValue(AttributeValue $attributeValue, array $data): AttributeValue
    {
        $attributeValue->update($data);
        return $attributeValue->refresh();
    }

    /**
     * Delete an attribute value.
     */
    public function deleteAttributeValue(AttributeValue $attributeValue): bool
    {
        return $attributeValue->delete();
    }
}