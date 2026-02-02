<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreCategoryRequest;
use App\Http\Requests\V1\Admin\UpdateCategoryRequest;
use App\Http\Resources\V1\Admin\CategoryResource;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->authorizeResource(Category::class, 'category');
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories with their subcategories
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request, 20);

        return response()->api(CategoryResource::collection($categories),200);
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->api(new CategoryResource($category), 201);
    }

    /**
     * Display the specified category with its subcategories
     */
    public function show(Category $category): JsonResponse
    {
        $category = $this->categoryService->getCategory($category);

        return response()->api(new CategoryResource($category));
    }

    /**
     * Update the specified category
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->updateCategory($category, $request->validated());

        return response()->api(new CategoryResource($category));
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);

        return response()->api(null, 204);
    }
}