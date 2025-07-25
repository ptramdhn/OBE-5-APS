<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\GraduateProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GraduateProfilePolicy
{
    public function update(User $user, GraduateProfile $graduateProfile): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $graduateProfile->prodi_id;
    }

    public function delete(User $user, GraduateProfile $graduateProfile): bool
    {
        if (optional($user->role)->name === UserRoleEnum::SUPER_ADMIN->value) {
            return true;
        }

        return $user->prodi_id !== null && $user->prodi_id === $graduateProfile->prodi_id;
    }
}
