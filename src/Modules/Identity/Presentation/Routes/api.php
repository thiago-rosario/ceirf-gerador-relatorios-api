<?php

use Illuminate\Support\Facades\Route;
use src\Modules\Identity\Presentation\Http\Controllers\Auth\AuthenticateUserController;
use src\Modules\Identity\Presentation\Http\Controllers\Auth\CurrentUserController;
use src\Modules\Identity\Presentation\Http\Controllers\Auth\LogoutUserController;
use src\Modules\Identity\Presentation\Http\Controllers\Auth\ResetUserPasswordController;
use src\Modules\Identity\Presentation\Http\Controllers\User\CreateUserController;
use src\Modules\Identity\Presentation\Http\Controllers\User\DeactivateUserController;
use src\Modules\Identity\Presentation\Http\Controllers\User\FindByIdUserController;
use src\Modules\Identity\Presentation\Http\Controllers\User\GetRolesController;
use src\Modules\Identity\Presentation\Http\Controllers\User\ListAllUserController;
use src\Modules\Identity\Presentation\Http\Controllers\User\UpdateUserController;
use src\Modules\Identity\Presentation\Http\Middleware\EnsurePasswordIsChanged;

Route::post('/auth/login', AuthenticateUserController::class)
    ->middleware('throttle:auth-login')
    ->name('auth.login');

Route::get('/auth/me', CurrentUserController::class)
    ->middleware('auth:api')
    ->name('auth.me');

Route::middleware(['auth:api', EnsurePasswordIsChanged::class])->group(function (): void {
    Route::post('/auth/logout', LogoutUserController::class)->name('auth.logout');
    Route::patch('/users/{id}', UpdateUserController::class)->name('users.update');

    Route::middleware('can:manage-users')->group(function (): void {
        Route::get('/roles', GetRolesController::class)->name('roles.list');
        Route::post('/auth/reset-password/{id}', ResetUserPasswordController::class)->name('auth.reset-password');
        Route::post('/users', CreateUserController::class)->name('users.create');
        Route::get('/users', ListAllUserController::class)->name('users.list');
        Route::get('/users/search', FindByIdUserController::class)->name('users.search');
        Route::get('/users/{id}', FindByIdUserController::class)->name('users.find');
        Route::patch('/users/{id}/deactivate', DeactivateUserController::class)->name('users.deactivate');
    });
});
