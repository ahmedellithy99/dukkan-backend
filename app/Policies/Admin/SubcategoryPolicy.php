<?php

namespace App\Policies\Admin;

use App\Models\Subcategory;
use App\Models\User;

class SubcategoryPolicy
{
    /**
     * Determine whether the user can view any subcategories.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the subcategory.
     */
    public function view(User $user, Subcategory $subcategory): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create subcategories.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the subcategory.
     */
    public function update(User $user, Subcategory $subcategory): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the subcategory.
     */
    public function delete(User $user, Subcategory $subcategory): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the subcategory.
     */
    public function restore(User $user, Subcategory $subcategory): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the subcategory.
     */
    public function forceDelete(User $user, Subcategory $subcategory): bool
    {
        return $user->role === 'admin';
    }
}