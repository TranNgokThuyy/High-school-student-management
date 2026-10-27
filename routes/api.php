<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HocSinhController;
use Illuminate\Support\Facades\Route;

// API dùng session của Laravel để frontend React không phải lưu token nhạy cảm.
// Tên route có tiền tố "api." để không ghi đè route giao diện cùng tên (hoc-sinh.index, hoc-sinh.show...)
Route::middleware('web')->name('api.')->group(function () {
    Route::get('/csrf-token', [AuthController::class, 'csrfToken'])->name('csrf-token');
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:5,1');

    Route::middleware(['auth', 'check.active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/lop-hoc', [HocSinhController::class, 'classes'])->name('lop-hoc');
        Route::apiResource('hoc-sinh', HocSinhController::class);
    });
});
