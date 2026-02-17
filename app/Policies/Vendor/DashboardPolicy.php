<?php

namespace App\Policies\Vendor;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Determine whether the user can view dashboard statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->role === 'vendor';
    }

    /**
     * Determine whether the user can view recent activity.
     */
    public function viewActivity(User $user): bool
    {
        return $user->role === 'vendor';
    }
}
