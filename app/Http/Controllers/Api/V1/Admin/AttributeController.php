<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreAttributeRequest;
use App\Http\Requests\V1\Admin\UpdateAttributeRequest;
use App\Http\Resources\V1\Admin\AttributeResource;
use App\Models\Attribute;
use App\Services\Admin\AttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    protected AttributeService $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * Display a listing of attributes with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $attributes = $this->attributeService->getAttributes($request);

        return response()->api(AttributeResource::collection($attributes),200);
    }

    /**
     * Store a newly created attribute
     */
    public function store(StoreAttributeRequest $request): JsonResponse
    {
        $attribute = $this->attributeService->createAttribute($request->validated());

        return response()->api(new AttributeResource($attribute), 201);
    }

    /**
     * Display the specified attribute
     */
    public function show(Attribute $attribute): JsonResponse
    {
        $attribute = $this->attributeService->getAttribute($attribute);

        return response()->api(new AttributeResource($attribute));
    }

    /**
     * Update the specified attribute
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute): JsonResponse
    {
        $attribute = $this->attributeService->updateAttribute($attribute, $request->validated());

        return response()->api(new AttributeResource($attribute));
    }

    /**
     * Remove the specified attribute
     */
    public function destroy(Attribute $attribute)
    {
        $this->attributeService->deleteAttribute($attribute);
        
        return response()->api(null, 204);
    }
}