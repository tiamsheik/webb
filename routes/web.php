<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'ZigZack Streaming Platform API',
        'version' => '1.0.0',
        'status' => 'API is running'
    ]);
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Please use the API endpoints for authentication'
    ]);
})->name('login');

Route::get('/admin', function () {
    return response()->json([
        'message' => 'Admin dashboard would go here'
    ]);
})->name('dashboard');