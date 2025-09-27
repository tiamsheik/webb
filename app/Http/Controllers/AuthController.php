<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\UserService;
use App\Services\AuthService;
use App\Models\User;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private UserService $userService,
        private AuthService $authService
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $user = $this->userService->createUser($validatedData);
            $token = $this->authService->createAuthToken($user);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => 'An error occurred during registration'
            ], 500);
        }
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $user = $this->attemptLogin($request);

            if (!$user) {
                RateLimiter::hit($this->throttleKey($request));
                
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            RateLimiter::clear($this->throttleKey($request));

            $this->updateLoginStats($user, $request);

            $token = $this->authService->createAuthToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at,
                    'device_info' => [
                        'type' => $this->getDeviceType($request),
                        'name' => $this->getDeviceName($request),
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = $request->user();

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $this->formatUserData($user->fresh())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = $request->user();

            if (Hash::check($validatedData['new_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from your current password'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);

            \Log::info('User changed password', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'changed_at' => now()
            ]);

            $currentToken = $request->user()->currentAccessToken();
            $request->user()->tokens()
                ->where('id', '!=', $currentToken->id)
                ->delete();

            Device::where('user_id', $user->user_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $currentDevice = Device::where('user_id', $user->user_id)
                ->where('device_type', $this->getDeviceType($request))
                ->where('device_name', $this->getDeviceName($request))
                ->first();
                
            if ($currentDevice) {
                $currentDevice->update(['is_active' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully. Other devices have been logged out.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Password change failed', [
                'user_id' => $request->user()->user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password change failed',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->revokeTokens($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['activeSubscription.plan', 'devices']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $this->formatUserData($user),
                    'subscription' => $user->activeSubscription,
                    'active_devices_count' => $user->devices->where('is_active', true)->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $request->user()->currentAccessToken()->delete();
            
            $token = $this->authService->createAuthToken($user, 'refreshed-token');
            
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDevices(Request $request): JsonResponse
    {
        try {
            $devices = $request->user()->devices()
                ->where('is_active', true)
                ->orderBy('last_active', 'desc')
                ->get(['device_id', 'device_name', 'device_type', 'last_active', 'is_active']);
                
            return response()->json([
                'success' => true,
                'data' => [
                    'devices' => $devices
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve devices: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logoutDevice(Request $request, $deviceId): JsonResponse
    {
        try {
            $deviceId = (int)$deviceId;
            if ($deviceId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid device ID'
                ], 400);
            }

            $device = Device::where('device_id', $deviceId)
                ->where('user_id', $request->user()->user_id)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }
            
            $device->markAsInactive();
            
            $request->user()->tokens()
                ->where('name', 'like', "%{$device->device_type}%")
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out from device successfully',
                'data' => [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logoutAllDevices(Request $request): JsonResponse
    {
        try {
            $this->authService->revokeAllTokens($request->user());
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function attemptLogin(Request $request): ?User
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return null;
        }

        if (!$this->isAccountActive($user)) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        return $user;
    }

    private function isAccountActive(User $user): bool
    {
        if (app()->environment('local', 'testing')) {
            return true;
        }
        
        return $user->email_verified_at !== null;
    }

    private function updateLoginStats(User $user, Request $request): void
    {
        $user->update([
            'last_login_at' => now(),
        ]);

        $this->authService->registerDevice($user, [
            'device_type' => $this->getDeviceType($request),
            'device_name' => $this->getDeviceName($request),
        ]);
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }

    private function getDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (strpos($userAgent, 'Mobile') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false) {
            return 'tablet';
        } elseif (strpos($userAgent, 'TV') !== false || strpos($userAgent, 'SmartTV') !== false) {
            return 'smart_tv';
        } else {
            return 'web';
        }
    }

    private function getDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        if (preg_match('/\((.*?)\)/', $userAgent, $matches)) {
            return substr($matches[1] ?? 'Unknown Device', 0, 50);
        }

        return 'Web Browser';
    }

    private function formatUserData(User $user): array
    {
        return [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'avatar_url' => $user->avatar_url,
            'subscription_status' => $user->subscription_status,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}