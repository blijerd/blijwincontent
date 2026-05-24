<?php

namespace App\Actions\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateFirstUserAction
{
    /**
     * @param array{name: string, email: string, password: string} $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            if (User::query()->exists()) {
                throw new RuntimeException('The first user has already been created.');
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            ActivityLog::query()->create([
                'user_id' => $user->id,
                'event' => 'first_user_created',
                'subject_type' => $user->getMorphClass(),
                'subject_id' => $user->getKey(),
                'properties' => [
                    'email' => $user->email,
                    'source' => 'setup',
                ],
            ]);

            return $user;
        });
    }
}
