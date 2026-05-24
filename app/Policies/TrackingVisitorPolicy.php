<?php

namespace App\Policies;

use App\Models\TrackingVisitor;
use App\Models\User;

class TrackingVisitorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrackingVisitor $trackingVisitor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TrackingVisitor $trackingVisitor): bool
    {
        return false;
    }

    public function delete(User $user, TrackingVisitor $trackingVisitor): bool
    {
        return false;
    }
}
