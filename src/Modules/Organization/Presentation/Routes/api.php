<?php

use Illuminate\Support\Facades\Route;
use src\Modules\Identity\Presentation\Http\Middleware\EnsurePasswordIsChanged;
use src\Modules\Organization\Presentation\Http\Controllers\Coordination\GetCoordinationsController;

Route::middleware(['auth:api', EnsurePasswordIsChanged::class, 'can:manage-users'])->group(function (): void {
    Route::get('/coordinations', GetCoordinationsController::class)->name('coordinations.list');
});
