<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function update(User $user, User $model): bool
    {
        return optional($user->role)->name === 'Super Admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return optional($user->role)->name === 'Super Admin';
    }
}
