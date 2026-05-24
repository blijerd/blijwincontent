<?php

namespace App\Policies;

use App\Models\NavigationMenu;
use App\Models\User;

class NavigationMenuPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NavigationMenu $navigationMenu): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, NavigationMenu $navigationMenu): bool
    {
        return true;
    }

    public function delete(User $user, NavigationMenu $navigationMenu): bool
    {
        return true;
    }

    public function restore(User $user, NavigationMenu $navigationMenu): bool
    {
        return true;
    }

    public function forceDelete(User $user, NavigationMenu $navigationMenu): bool
    {
        return true;
    }
}
