<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index']);

Route::get('/api/products', [ProductController::class, 'list']);
Route::post('/api/products', [ProductController::class, 'store']);
Route::put('/api/products/{product}', [ProductController::class, 'update']);
Route::delete('/api/products/{product}', [ProductController::class, 'destroy']);

Route::post('/api/products/import', [ProductController::class, 'import']);
Route::get('/api/products/export', [ProductController::class, 'export']);
