<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\StudyMaterial;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudyMaterialPolicy
{
    public function update(User $user, StudyMaterial $studyMaterial): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $studyMaterial->prodi_id;
    }

    public function delete(User $user, StudyMaterial $studyMaterial): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $studyMaterial->prodi_id;
    }
}
