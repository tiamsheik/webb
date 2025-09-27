<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

Route::get('/test-db', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        
        return response()->json([
            'success' => true,
            'message' => 'Database connection successful',
            'database' => 'SQLite'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ], 500);
    }
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
     Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/devices', [AuthController::class, 'getDevices']);
        Route::post('/logout-device/{device}', [AuthController::class, 'logoutDevice']);
        Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);
    });
    
    Route::prefix('user')->group(function () {
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::post('/logout-device/{device}', [AuthController::class, 'logoutDevice'])
    ->where('device', '[0-9]+');
});