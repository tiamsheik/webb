<?php

namespace App\Services;

use App\Models\User;
use App\Models\Device;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function createAuthToken(User $user, string $tokenName = 'auth-token'): NewAccessToken
    {
        
        return $user->createToken($tokenName, ['*'], now()->addDays(7));
    }

    public function registerDevice(User $user, array $deviceData): Device
    {
        $device = Device::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'device_type' => $deviceData['device_type'],
                'device_name' => $deviceData['device_name'] ?? $deviceData['device_type'],
            ],
            [
                'last_active' => now(),
                'is_active' => true,
            ]
        );

        return $device;
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
        
        Device::where('user_id', $user->user_id)
              ->update(['is_active' => false]);
    }

    public function revokeDeviceTokens(User $user, string $deviceType, string $deviceName): void
    {
        Device::where('user_id', $user->user_id)
              ->where('device_type', $deviceType)
              ->where('device_name', $deviceName)
              ->update(['is_active' => false]);
              
    }

    public function needsRehash(User $user, string $password): bool
    {
        return Hash::needsRehash($user->password);
    }

    public function isPasswordStrong(string $password): bool
    {
        // minimum 8 characters, at least one letter and one number
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/', $password);
    }

    public function generateSecureToken(int $length = 64): string
    {
        return Str::random($length);
    }
}