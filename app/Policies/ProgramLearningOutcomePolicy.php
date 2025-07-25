<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\ProgramLearningOutcome;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProgramLearningOutcomePolicy
{
    public function update(User $user, ProgramLearningOutcome $programLearningOutcome): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $programLearningOutcome->prodi_id;
    }

    public function delete(User $user, ProgramLearningOutcome $programLearningOutcome): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $programLearningOutcome->prodi_id;
    }
}
