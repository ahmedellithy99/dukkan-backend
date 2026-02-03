<?php

namespace App\Policies\Admin;

use App\Models\Attribute;
use App\Models\User;

class AttributePolicy
{
    /**
     * Determine whether the user can view any attributes.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the attribute.
     */
    public function view(User $user, Attribute $attribute): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create attributes.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the attribute.
     */
    public function update(User $user, Attribute $attribute): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the attribute.
     */
    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the attribute.
     */
    public function restore(User $user, Attribute $attribute): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the attribute.
     */
    public function forceDelete(User $user, Attribute $attribute): bool
    {
        return $user->role === 'admin';
    }
}