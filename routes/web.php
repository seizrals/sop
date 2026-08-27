<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
    
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('sop')->name('sop.')->group(function () {
        Route::get('/', [SopController::class, 'index'])->name('index');
        Route::get('/team/{team}', [SopController::class, 'team'])->name('team');
        Route::post('/team/{team}/activity', [SopController::class, 'storeActivity'])->name('activity.store');
        Route::post('/team/{team}/executor', [SopController::class, 'storeMasterExecutor'])->name('executor.store');
        Route::get('/team/{team}/activity/{activity}', [SopController::class, 'activity'])->name('activity');
        Route::get('/team/{team}/activity/{activity}/create', [SopController::class, 'create'])->name('create');
        Route::post('/team/{team}/activity/{activity}', [SopController::class, 'store'])->name('store');
        Route::post('/team/{team}/activity/{activity}/preview-download', [SopController::class, 'previewDownload'])->name('preview-download');

        Route::get('/document/{document}/edit', [SopController::class, 'edit'])->name('edit');
        Route::patch('/document/{document}', [SopController::class, 'update'])->name('update');
        Route::post('/document/{document}/revise', [SopController::class, 'revise'])->name('revise');
        Route::delete('/document/{document}', [SopController::class, 'destroy'])->name('destroy');
        Route::get('/document/{document}/download', [SopController::class, 'download'])->name('download');
        Route::get('/document/{document}/preview', [SopController::class, 'preview'])->name('preview');
        Route::post('/document/{document}/save-template', [SopController::class, 'saveTemplate'])->name('save-template');
    });

    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('archives')->name('archives.')->group(function () {
        Route::get('/', [ArchiveController::class, 'index'])->name('index');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::patch('/{user}', [UserManagementController::class, 'update'])->name('update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
