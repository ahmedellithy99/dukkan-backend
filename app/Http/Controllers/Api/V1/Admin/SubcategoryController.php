<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreSubcategoryRequest;
use App\Http\Requests\V1\Admin\UpdateSubcategoryRequest;
use App\Http\Resources\V1\Admin\SubcategoryResource;
use App\Http\Resources\V1\Admin\CategoryResource;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\Admin\SubcategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubcategoryController extends Controller
{
    protected SubcategoryService $subcategoryService;

    public function __construct(SubcategoryService $subcategoryService)
    {
        $this->authorizeResource(Subcategory::class);
        $this->subcategoryService = $subcategoryService;
    }

    /**
     * Display a listing of subcategories
     */
    public function index(Request $request, Category $category): JsonResponse
    {
        $subcategories = $this->subcategoryService->getSubcategories($category, $request);

        return response()->api(SubcategoryResource::collection($subcategories), 200);
    }

    /**
     * Store a newly created subcategory
     */
    public function store(StoreSubcategoryRequest $request, Category $category): JsonResponse
    {
        $subcategory = $this->subcategoryService->createSubcategory($category, $request->validated());
        return response()->api(new SubcategoryResource($subcategory), 201);
    }

    /**
     * Display the specified subcategory
     */
    public function show(Category $category, Subcategory $subcategory): JsonResponse
    {
        $subcategory = $this->subcategoryService->getSubcategory($subcategory);

        return response()->api(new SubcategoryResource($subcategory));
    }

    /**
     * Update the specified subcategory
     */
    public function update(UpdateSubcategoryRequest $request, Category $category, Subcategory $subcategory): JsonResponse
    {
        $subcategory = $this->subcategoryService->updateSubcategory($subcategory, $request->validated());

        return response()->api(new SubcategoryResource($subcategory));
    }

    /**
     * Remove the specified subcategory
     */
    public function destroy(Category $category,Subcategory $subcategory)
    {
        $this->subcategoryService->deleteSubcategory($subcategory);

        return response()->api(null, 204);
    }
}
