<?php

namespace App\Policies\Admin;

use App\Models\AttributeValue;
use App\Models\User;

class AttributeValuePolicy
{
    /**
     * Determine whether the user can view any attribute values.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the attribute value.
     */
    public function view(User $user, AttributeValue $attributeValue): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create attribute values.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the attribute value.
     */
    public function update(User $user, AttributeValue $attributeValue): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the attribute value.
     */
    public function delete(User $user, AttributeValue $attributeValue): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the attribute value.
     */
    public function restore(User $user, AttributeValue $attributeValue): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the attribute value.
     */
    public function forceDelete(User $user, AttributeValue $attributeValue): bool
    {
        return $user->role === 'admin';
    }
}