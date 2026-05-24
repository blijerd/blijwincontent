<?php

namespace App\Policies;

use App\Models\DownloadItem;
use App\Models\User;

class DownloadItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DownloadItem $downloadItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DownloadItem $downloadItem): bool
    {
        return true;
    }

    public function delete(User $user, DownloadItem $downloadItem): bool
    {
        return true;
    }

    public function restore(User $user, DownloadItem $downloadItem): bool
    {
        return true;
    }

    public function forceDelete(User $user, DownloadItem $downloadItem): bool
    {
        return true;
    }
}
