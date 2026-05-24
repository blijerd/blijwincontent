<?php

namespace App\Actions\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResetAdminPasswordAction
{
    public function execute(User $user, string $password): User
    {
        return DB::transaction(function () use ($user, $password): User {
            $user->forceFill([
                'password' => $password,
                'email_verified_at' => $user->email_verified_at ?? now(),
                'remember_token' => null,
            ])->save();

            ActivityLog::query()->create([
                'user_id' => $user->id,
                'event' => 'admin_password_reset',
                'subject_type' => $user->getMorphClass(),
                'subject_id' => $user->getKey(),
                'properties' => [
                    'email' => $user->email,
                    'source' => 'artisan',
                ],
            ]);

            return $user->refresh();
        });
    }
}
