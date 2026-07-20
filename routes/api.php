<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HocSinhController;
use Illuminate\Support\Facades\Route;

// API dùng session của Laravel để frontend React không phải lưu token nhạy cảm.
Route::middleware('web')->group(function () {
    Route::get('/csrf-token', [AuthController::class, 'csrfToken']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth', 'check.active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/lop-hoc', [HocSinhController::class, 'classes']);
        Route::apiResource('hoc-sinh', HocSinhController::class);
    });
});
