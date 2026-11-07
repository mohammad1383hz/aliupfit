<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::post('/forms', [PublicSiteController::class, 'submit'])->middleware('throttle:8,1')->name('forms.submit');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');

    Route::middleware(['auth', EnsureAdmin::class])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::patch('/submissions/{submission}', [AdminController::class, 'updateSubmission'])->name('submissions.update');
        Route::delete('/submissions/{submission}', [AdminController::class, 'deleteSubmission'])->name('submissions.delete');
    });
});
