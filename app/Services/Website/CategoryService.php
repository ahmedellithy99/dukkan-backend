<?php

namespace App\Services\Website;

use App\Models\Category;
use App\Filters\Website\CategoryFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all categories with their subcategories for public display
     */
    public function getCategories(Request $request): Collection
    {
        return Category::with('subcategories')
            ->filter(new CategoryFilter($request))
            ->get();
    }

    /**
     * Get a single category with its subcategories for public display
     */
    public function getCategory(Category $category): Category
    {
        return $category->load('subcategories');
    }
}