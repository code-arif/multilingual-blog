<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('/password/reset', [AuthController::class, 'resetPassword']);
        Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
    });

    // Public post routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);

    // Public category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}/posts', [CategoryController::class, 'posts']);

    // Search
    Route::get('/search', [SearchController::class, 'search']);

    // Protected routes
    Route::middleware(['auth:api'])->group(function () {

        // Auth management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'updatePassword']);
        });

        // Admin-only routes
        Route::middleware(['role:admin'])->group(function () {

            // Post management (admin)
            Route::post('/posts', [PostController::class, 'store']);
            Route::put('/posts/{id}', [PostController::class, 'update']);
            Route::delete('/posts/{id}', [PostController::class, 'destroy']);
            Route::patch('/posts/{id}/publish', [PostController::class, 'publish']);
            Route::patch('/posts/{id}/unpublish', [PostController::class, 'unpublish']);

            // Category management (admin)
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            // Image upload
            Route::post('/images/upload', [ImageController::class, 'upload']);
            Route::delete('/images/{filename}', [ImageController::class, 'destroy']);

            // User management (admin)
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);
            Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);

            // Admin dashboard stats
            Route::get('/admin/stats', [UserController::class, 'dashboardStats']);
        });
    });
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => true,
        'message' => 'API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
});
