<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

Route::get('/api/products', [ProductController::class, 'list']);

Route::post('/api/products', [ProductController::class, 'store']);

Route::put('/api/products/{product}', [ProductController::class, 'update']);

Route::delete('/api/products/{product}', [ProductController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Excel Import / Export
|--------------------------------------------------------------------------
*/

Route::post('/api/products/import', [ProductController::class, 'import']);

Route::get('/api/products/export', [ProductController::class, 'export']);

Route::get(
    '/api/products/export-filtered',
    [ProductController::class, 'exportFiltered']
);