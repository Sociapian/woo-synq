<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Product routes
    Route::apiResource('api/products', ProductController::class);
    Route::post('/api/products/{product}/retry-sync', [ProductController::class, 'retrySync']);
    
    // WooCommerce test route
    Route::post('/woocommerce/test', function () {
        $woocommerce = new \App\Services\WooCommerceService();
        $result = $woocommerce->testConnection();
        return response()->json($result);
    });
});

// Web interface routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth:sanctum');

Route::get('/products', function () {
    return view('products');
})->middleware('auth:sanctum');
