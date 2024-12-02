<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function get(User $user, Report $report): bool
    {
        return $user->id === $report->user;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->id === $report->user;
    }
}
