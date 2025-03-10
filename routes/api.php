<?php
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/stats', [App\Http\Controllers\DashboardApiController::class, 'stats'])
        ->name('api.dashboard.stats');
    Route::get('/activity', [App\Http\Controllers\DashboardApiController::class, 'activity'])
        ->name('api.dashboard.activity');
    Route::get('/upcoming-tasks', [App\Http\Controllers\DashboardApiController::class, 'upcomingTasks'])
        ->name('api.dashboard.upcoming-tasks');
    Route::get('/announcements', [App\Http\Controllers\DashboardApiController::class, 'announcements'])
        ->name('api.dashboard.announcements');
});
