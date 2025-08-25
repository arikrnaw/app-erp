<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::get('settings/general', function () {
        return Inertia::render('settings/General');
    })->name('general');

    Route::get('settings/users', function () {
        return Inertia::render('settings/Users');
    })->name('users');

    Route::get('settings/system', function () {
        return Inertia::render('settings/System');
    })->name('system');

    Route::get('settings/backup', function () {
        return Inertia::render('settings/Backup');
    })->name('backup');
});
