<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Filters\Admin\CategoryFilter;
use App\Exceptions\Domain\Category\CategoryInUseException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get categories with optional filtering and pagination
     */
    public function getCategories(Request $request, int $perPage = 20): LengthAwarePaginator|Collection
    {
        return Category::with('subcategories')
            ->filter(new CategoryFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single category with relationships
     */
    public function getCategory(Category $category): Category
    {   
        return $category->load('subcategories');
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data): Category
    {
        $category = Category::create($data);
        return $category->load('subcategories');
    }

    /**
     * Update an existing category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        $category->refresh();
        $category->load('subcategories');

        return $category;
    }

    /**
     * Delete a category
     * 
     * @throws CategoryInUseException When category contains subcategories with products
     */
    public function deleteCategory(Category $category): void
    {
        // Check if category can be deleted (no products in subcategories)
        if (!$category->canBeDeleted()) {
            throw new CategoryInUseException();
        }

        $category->delete();
    }
}
