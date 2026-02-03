<?php

namespace App\Services\Admin;

use App\Models\Subcategory;
use App\Models\Category;
use App\Filters\Admin\SubcategoryFilter;
use App\Exceptions\Domain\Subcategory\SubcategoryInUseException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class SubcategoryService
{
    /**
     * Get subcategories with optional filtering and pagination
     */
    public function getSubcategories(Category $category, Request $request): Collection
    {
        return Subcategory::where('category_id', $category->id)
            ->filter(new SubcategoryFilter($request))
            ->get();
    }

    /**
     * Get a single subcategory with relationships
     */
    public function getSubcategory(Subcategory $subcategory): Subcategory
    {
        return $subcategory;
    }

    /**
     * Create a new subcategory
     */
    public function createSubcategory(Category $category, array $data): Subcategory
    {   
        return $category->subcategories()->create($data);
        
    }

    /**
     * Update an existing subcategory
     */
    public function updateSubcategory(Subcategory $subcategory, array $data): Subcategory
    {
        $subcategory->update($data);
        
        return $subcategory->refresh();
    }

    /**
     * Delete a subcategory
     * 
     * @throws SubcategoryInUseException When subcategory contains products
     */
    public function deleteSubcategory(Subcategory $subcategory): void
    {
        // Check if subcategory can be deleted (no products)
        if (!$subcategory->canBeDeleted()) {
            throw new SubcategoryInUseException();
        }

        $subcategory->delete();
    }
}