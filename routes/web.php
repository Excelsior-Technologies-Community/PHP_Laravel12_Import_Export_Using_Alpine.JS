<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get(
    '/',
    [ProductController::class, 'index']
);

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/products',
    [ProductController::class, 'list']
);

Route::get(
    '/api/products/statistics',
    [ProductController::class, 'statistics']
);

Route::post(
    '/api/products',
    [ProductController::class, 'store']
);

Route::put(
    '/api/products/{product}',
    [ProductController::class, 'update']
);

Route::delete(
    '/api/products/{product}',
    [ProductController::class, 'destroy']
);

/*
|--------------------------------------------------------------------------
| New Bulk / Duplicate Features
|--------------------------------------------------------------------------
*/

Route::post(
    '/api/products/bulk-delete',
    [ProductController::class, 'bulkDelete']
);

Route::post(
    '/api/products/bulk-status',
    [ProductController::class, 'bulkStatus']
);

Route::post(
    '/api/products/{product}/duplicate',
    [ProductController::class, 'duplicate']
);

/*
|--------------------------------------------------------------------------
| Excel Import / Export
|--------------------------------------------------------------------------
*/

Route::post(
    '/api/products/import',
    [ProductController::class, 'import']
);

Route::post(
    '/api/products/import-direct',
    [ProductController::class, 'importDirect']
);

Route::get(
    '/api/products/export',
    [ProductController::class, 'export']
);

Route::get(
    '/api/products/export-filtered',
    [ProductController::class, 'exportFiltered']
);

Route::get(
    '/api/products/export-custom',
    [ProductController::class, 'exportCustom']
);