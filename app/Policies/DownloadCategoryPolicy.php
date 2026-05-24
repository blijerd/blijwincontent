<?php

namespace App\Policies;

use App\Models\DownloadCategory;
use App\Models\User;

class DownloadCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DownloadCategory $downloadCategory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DownloadCategory $downloadCategory): bool
    {
        return true;
    }

    public function delete(User $user, DownloadCategory $downloadCategory): bool
    {
        return true;
    }

    public function restore(User $user, DownloadCategory $downloadCategory): bool
    {
        return true;
    }

    public function forceDelete(User $user, DownloadCategory $downloadCategory): bool
    {
        return true;
    }
}
