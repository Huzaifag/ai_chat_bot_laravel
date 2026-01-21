<?php

use App\Http\Controllers\AiApiConfigController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});


//optimization

Route::get('/optimize', function () {
    \Artisan::call('optimize:clear');
    \Artisan::call('config:cache');
    \Artisan::call('route:cache');
    \Artisan::call('view:cache');
    return "Optimization commands executed.";
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminController::class, 'login']);
    });

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
        Route::get('/upload', [AdminController::class, 'upload'])->name('upload');
        Route::get('/chatlogs', [AdminController::class, 'chatlogs'])->name('chatlogs');
        Route::get('/chatlogs/{session}', [AdminController::class, 'chatlogDetail'])->name('chatlogs.show');
        Route::get('/ai-api-configs', [AiApiConfigController::class, 'index'])->name('ai-api-configs');
    });


    // Document Resource Routes
    Route::resource('documents', DocumentController::class)->middleware('admin.auth');
    Route::resource('ai-api-configs', AiApiConfigController::class)->middleware('admin.auth');
});

// Chat API Routes (public access for frontend)
Route::prefix('api/chat')->group(function () {
    Route::get('/messages', [ChatController::class, 'getMessages']);
    Route::post('/send', [ChatController::class, 'sendMessage']);
    Route::post('/process-session', [ChatController::class, 'processSessionMessages']);
});
