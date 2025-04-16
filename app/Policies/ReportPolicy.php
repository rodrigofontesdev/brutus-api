<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        return $user->id === $report->user;
    }

    public function update(User $user, Report $report): bool
    {
        return $user->id === $report->user;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->id === $report->user;
    }
}
