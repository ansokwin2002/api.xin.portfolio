<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LogoClientController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/contacts', [ContactController::class, 'store']);

// Public Logo Client routes
Route::get('/logo-clients', [LogoClientController::class, 'index']);
Route::get('/logo-clients/{id}', [LogoClientController::class, 'show']);

// Public Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Contact management (protected)
    Route::controller(ContactController::class)->group(function () {
        Route::get('/contacts', 'index');
        Route::get('/contacts/{id}', 'show');
        Route::put('/contacts/{id}', 'update');
        Route::delete('/contacts/{id}', 'destroy');
    });

    // Logo Client management (protected)
    Route::controller(LogoClientController::class)->group(function () {
        Route::post('/logo-clients', 'store');
        Route::put('/logo-clients/{id}', 'update');
        Route::delete('/logo-clients/{id}', 'destroy');
    });

    // Product management (protected)
    Route::controller(ProductController::class)->group(function () {
        Route::post('/products', 'store');
        Route::put('/products/{id}', 'update');
        Route::delete('/products/{id}', 'destroy');
    });

    // Example of another protected route
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to the dashboard. You are authenticated!',
        ]);
    });
});
