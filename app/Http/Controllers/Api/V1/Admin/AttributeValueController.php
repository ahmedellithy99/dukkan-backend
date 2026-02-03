<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreAttributeValueRequest;
use App\Http\Requests\V1\Admin\UpdateAttributeValueRequest;
use App\Http\Resources\V1\Admin\AttributeValueResource;
use App\Models\AttributeValue;
use App\Services\Admin\AttributeValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    protected AttributeValueService $attributeValueService;

    public function __construct(AttributeValueService $attributeValueService)
    {
        $this->attributeValueService = $attributeValueService;
    }

    /**
     * Display a listing of attribute values with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $attributeValues = $this->attributeValueService->getAttributeValues($request);

        return response()->api(AttributeValueResource::collection($attributeValues),200);
    }

    /**
     * Store a newly created attribute value
     */
    public function store(StoreAttributeValueRequest $request): JsonResponse
    {
        $attributeValue = $this->attributeValueService->createAttributeValue($request->validated());

        return response()->api(new AttributeValueResource($attributeValue), 201);
    }

    /**
     * Display the specified attribute value
     */
    public function show(AttributeValue $attribute_value): JsonResponse
    {
        $attributeValue = $this->attributeValueService->getAttributeValue($attribute_value);

        return response()->api(new AttributeValueResource($attributeValue));
    }

    /**
     * Update the specified attribute value
     */
    public function update(UpdateAttributeValueRequest $request, AttributeValue $attribute_value): JsonResponse
    {
        $attributeValue = $this->attributeValueService->updateAttributeValue($attribute_value, $request->validated());

        return response()->api(new AttributeValueResource($attributeValue));
    }

    /**
     * Remove the specified attribute value
     */
    public function destroy(AttributeValue $attribute_value)
    {
        $this->attributeValueService->deleteAttributeValue($attribute_value);

        return response()->api(null, 204);
    }
}