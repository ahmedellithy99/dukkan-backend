<?php

namespace App\Services\Website;

use App\Models\Subcategory;
use App\Filters\Website\SubcategoryFilter;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class SubcategoryService
{
    /**
     * Get all subcategories with their categories for public display
     */
    public function getSubcategories(Category $category, Request $request): Collection
    {
        return Subcategory::where('category_id', $category->id)
            ->filter(new SubcategoryFilter($request))
            ->get();

    }

    /**
     * Get a single subcategory with its category for public display
     */
    public function getSubcategory(Subcategory $subcategory): Subcategory
    {
        return $subcategory;
    }
}