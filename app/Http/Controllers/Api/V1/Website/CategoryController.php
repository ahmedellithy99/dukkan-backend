<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\CategoryResource;
use App\Models\Category;
use App\Services\Website\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories with their subcategories (public endpoint)
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request);

        return response()->api(CategoryResource::collection($categories),200);
    }

    /**
     * Display the specified category with its subcategories (public endpoint)
     */
    public function show(Category $category): JsonResponse
    {
        $category = $this->categoryService->getCategory($category);
        
        return response()->api(new CategoryResource($category));
    }
}