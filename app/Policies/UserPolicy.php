<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function get(User $user, User $profile): bool
    {
        return $user->id === $profile->id;
    }

    public function update(User $user, User $profile): bool
    {
        return $user->id === $profile->id;
    }
}
