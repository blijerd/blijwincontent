<?php

namespace App\Policies;

use App\Models\FaqCategory;
use App\Models\User;

class FaqCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function delete(User $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function restore(User $user, FaqCategory $faqCategory): bool
    {
        return true;
    }

    public function forceDelete(User $user, FaqCategory $faqCategory): bool
    {
        return true;
    }
}
