<?php

namespace App\Policies\Admin;

use App\Models\AdCarousel;
use App\Models\User;

class AdCarouselPolicy
{
    /**
     * Determine whether the user can view any ad carousels.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the ad carousel.
     */
    public function view(User $user, AdCarousel $adCarousel): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create ad carousels.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the ad carousel.
     */
    public function update(User $user, AdCarousel $adCarousel): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the ad carousel.
     */
    public function delete(User $user, AdCarousel $adCarousel): bool
    {
        return $user->role === 'admin';
    }
}
