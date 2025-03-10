<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskLogController;
use App\Http\Controllers\UserController;
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
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/projects/{project}/tasks/create', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/projects/{project}/tasks/{task}/log', [TaskLogController::class, 'index'])->name('tasks.logs.index');
    Route::post('/projects/{project}/tasks/{task}/log', [TaskLogController::class, 'store'])->name('tasks.logs.store');
    Route::delete('/projects/{project}/tasks/{task}/log/{log}', [TaskLogController::class, 'destroy'])->name('tasks.logs.destroy');
    Route::put('/projects/{project}/tasks/{task}/log/{log}', [TaskLogController::class, 'update'])->name('tasks.logs.update');

    Route::get('/projects/{project}/tasks/{task}/comments', [TaskCommentController::class, 'index'])->name('tasks.comments.index');
    Route::post('/projects/{project}/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/projects/{project}/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');

    Route::middleware('admin')
        ->prefix('admin')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::group(['prefix' => 'settings'], function () {
                Route::get('/', [AdminDashboardController::class, 'globalSettingsIndex'])->name('admin.settings.index');
                Route::put('/', [AdminDashboardController::class, 'globalSettingsUpdate'])->name('admin.settings.update');

                Route::get('/users', [AdminDashboardController::class, 'users'])->name('admin.users.index');
                Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');

                Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
                Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

                Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
                Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
                Route::put('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
                Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
            });
        });
});

require __DIR__.'/auth.php';
