<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'role' => 'subscriber',
            'avatar_url' => $this->faker->imageUrl(100, 100, 'people'),
            'subscription_status' => 'inactive',
            'email_verified_at' => now(),
            'last_login_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
            ];
        });
    }

    public function contentManager()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'content_manager',
            ];
        });
    }

    public function withSubscription()
    {
        return $this->state(function (array $attributes) {
            return [
                'subscription_status' => 'active',
            ];
        });
    }
}