<?php

namespace App\Policies;

use App\Models\DownloadFormat;
use App\Models\User;

class DownloadFormatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DownloadFormat $downloadFormat): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DownloadFormat $downloadFormat): bool
    {
        return true;
    }

    public function delete(User $user, DownloadFormat $downloadFormat): bool
    {
        return true;
    }

    public function restore(User $user, DownloadFormat $downloadFormat): bool
    {
        return true;
    }

    public function forceDelete(User $user, DownloadFormat $downloadFormat): bool
    {
        return true;
    }
}
