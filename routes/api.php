<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
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

    // Example of another protected route
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to the dashboard. You are authenticated!',
        ]);
    });
});
