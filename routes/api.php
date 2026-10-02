<?php

use App\Http\Controllers\Identity\Auth\AuthenticateUserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', AuthenticateUserController::class)->name('auth.login');
