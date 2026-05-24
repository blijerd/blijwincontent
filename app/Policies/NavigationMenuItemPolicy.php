<?php

namespace App\Policies;

use App\Models\NavigationMenuItem;
use App\Models\User;

class NavigationMenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NavigationMenuItem $navigationMenuItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, NavigationMenuItem $navigationMenuItem): bool
    {
        return true;
    }

    public function delete(User $user, NavigationMenuItem $navigationMenuItem): bool
    {
        return true;
    }

    public function restore(User $user, NavigationMenuItem $navigationMenuItem): bool
    {
        return true;
    }

    public function forceDelete(User $user, NavigationMenuItem $navigationMenuItem): bool
    {
        return true;
    }
}
