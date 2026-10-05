<?php

use App\Http\Controllers\Identity\Auth\AuthenticateUserController;
use App\Http\Controllers\Identity\Auth\LogoutUserController;
use App\Http\Controllers\Identity\Auth\ResetUserPasswordController;
use App\Http\Controllers\Identity\User\CreateUserController;
use App\Http\Controllers\Identity\User\DeactivateUserController;
use App\Http\Controllers\Identity\User\FindByIdUserController;
use App\Http\Controllers\Identity\User\ListAllUserController;
use App\Http\Controllers\Identity\User\UpdateUserController;
use App\Http\Middleware\Identity\EnsurePasswordIsChanged;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', AuthenticateUserController::class)
    ->middleware('throttle:auth-login')
    ->name('auth.login');

Route::middleware(['auth:api', EnsurePasswordIsChanged::class])->group(function (): void {
    Route::post('/auth/logout', LogoutUserController::class)->name('auth.logout');
    Route::patch('/users/{id}', UpdateUserController::class)->name('users.update');

    Route::middleware('can:manage-users')->group(function (): void {
        Route::post('/auth/reset-password/{id}', ResetUserPasswordController::class)->name('auth.reset-password');
        Route::post('/users', CreateUserController::class)->name('users.create');
        Route::get('/users', ListAllUserController::class)->name('users.list');
        Route::get('/users/search', FindByIdUserController::class)->name('users.search');
        Route::get('/users/{id}', FindByIdUserController::class)->name('users.find');
        Route::patch('/users/{id}/deactivate', DeactivateUserController::class)->name('users.deactivate');
    });
});
