<?php

namespace App\Policies;

use App\Models\MeiCategory;
use App\Models\User;

class MeiCategoryPolicy
{
    public function view(User $user, MeiCategory $category): bool
    {
        return $user->id === $category->user;
    }

    public function delete(User $user, MeiCategory $category): bool
    {
        return $user->id === $category->user;
    }
}
