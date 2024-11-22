<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function() {
        return redirect()->route('dashboard');
    });
    Route::get('/timesheet', [DashboardController::class, 'timesheet'])->name('timesheet');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{api_name}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{api_name}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{api_name}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/projects/{api_name}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/projects/{api_name}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/projects/{api_name}/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/projects/{api_name}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/projects/{api_name}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::middleware('admin')
        ->prefix('admin')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::group(['prefix' => 'settings'], function () {
                Route::get('/', [AdminDashboardController::class, 'settings'])->name('admin.settings.index');
                Route::put('/', [AdminDashboardController::class, 'updateSettings'])->name('admin.settings.update');

                Route::get('/users', [AdminDashboardController::class, 'users'])->name('admin.users.index');
                Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('admin.users.store');

                Route::get('/users/{user}', [AdminDashboardController::class, 'editUser'])->name('admin.users.edit');
                Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('admin.users.update');
                Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('admin.users.destroy');

                Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
                Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
                Route::get('/roles/{role}', [RoleController::class, 'edit'])->name('admin.roles.edit');
                Route::put('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
                Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
            });
        });
});

require __DIR__.'/auth.php';
