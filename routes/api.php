<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AccountController;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Account Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/accounts/{id}/update-information', [AccountController::class, 'updateInformation']);
    Route::put('/accounts/{id}/update-status', [AccountController::class, 'updateStatus']);
    Route::put('/accounts/{id}/update-password', [AccountController::class, 'updatePassword']);
    Route::get('/user', [AccountController::class, 'current']);
    Route::get('/user/{id}', [AccountController::class, 'show']);
});

// Temporary bug fix sa Route [Login]
// Nag re-redirect sa login blade instead na json kaya eto nalang muna
Route::get('redirect', function () {
    return response()->json([
        'message'=>"unauthorized access"
    ], 401);
})->name('login');
