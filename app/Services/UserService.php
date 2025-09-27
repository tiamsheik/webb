<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(array $userData): User
    {
        return DB::transaction(function () use ($userData) {
            $user = User::create([
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'avatar_url' => $userData['avatar_url'] ?? null,
                'role' => 'subscriber',
                'subscription_status' => 'inactive',
            ]);

            // add welcome email, default preferences, and other here
            // $this->sendWelcomeEmail($user);

            return $user;
        });
    }

    public function sendWelcomeEmail(User $user): void
    {
        // add welcome email logic here
        // notification system
    }
}