<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\AttributeResource;
use App\Models\Attribute;
use App\Services\Website\AttributeService;
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
     * Display the specified attribute with its values
     */
    public function show(Attribute $attribute): JsonResponse
    {
        $attribute = $this->attributeService->getAttribute($attribute);

        return response()->api(new AttributeResource($attribute));
    }
}