<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\SubcategoryResource;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\Website\SubcategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubcategoryController extends Controller
{
    protected SubcategoryService $subcategoryService;

    public function __construct(SubcategoryService $subcategoryService)
    {
        $this->subcategoryService = $subcategoryService;
    }

    /**
     * Display a listing of subcategories (public endpoint)
     */
     public function index(Request $request, Category $category): JsonResponse
    {
        $subcategories = $this->subcategoryService->getSubcategories($category, $request);

        return response()->api(SubcategoryResource::collection($subcategories), 200);
    }

    /**
     * Display the specified subcategory (public endpoint)
     */
    public function show(Category $category, Subcategory $subcategory): JsonResponse
    {
        $subcategory = $this->subcategoryService->getSubcategory($subcategory);

        return response()->api(new SubcategoryResource($subcategory));
    }
}