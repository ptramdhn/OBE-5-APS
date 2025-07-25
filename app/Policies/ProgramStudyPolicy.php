<?php

namespace App\Policies;

use App\Models\ProgramStudy;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProgramStudyPolicy
{
    public function update(User $user, ProgramStudy $programStudy): bool
    {
        return optional($user->role)->name === 'SUPER ADMIN';
    }

    public function delete(User $user, ProgramStudy $programStudy): bool
    {
        return optional($user->role)->name === 'SUPER ADMIN';
    }
}
