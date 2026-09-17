<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display the product management page.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Return all products as JSON.
     */
    public function list()
    {
        return response()->json(
            Product::latest()->get()
        );
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $product = Product::create($validated);

        return response()->json($product);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Import products from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:5120',
            ],

            'import_mode' => [
                'required',
                'in:add_new,update_existing',
            ],
        ]);


        try {

            /*
            |--------------------------------------------------------------------------
            | Create Import Handler
            |--------------------------------------------------------------------------
            */

            $import = new ProductsImport(
                $request->input('import_mode')
            );


            /*
            |--------------------------------------------------------------------------
            | Import Excel
            |--------------------------------------------------------------------------
            */

            Excel::import(
                $import,
                $request->file('file')
            );


            /*
            |--------------------------------------------------------------------------
            | Import Mode Label
            |--------------------------------------------------------------------------
            */

            $modeLabel =
                $request->input('import_mode') === 'update_existing'
                    ? 'Update Existing'
                    : 'Add New';


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,

                'message' => 'Excel import completed successfully.',

                'mode' => $request->input('import_mode'),

                'mode_label' => $modeLabel,

                'created' => $import->createdCount,

                'updated' => $import->updatedCount,

                'imported' =>
                    $import->createdCount
                    +
                    $import->updatedCount,

                'skipped' => $import->skippedCount,

                'total' =>
                    $import->createdCount
                    +
                    $import->updatedCount
                    +
                    $import->skippedCount,

                'errors' => $import->errors,

                'activities' => $import->activities,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,

                'message' => 'Unable to import the Excel file.',

                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export all products.
     */
    public function export()
    {
        return Excel::download(
            new ProductsExport(),
            'products.xlsx'
        );
    }

    /**
     * Export products using current Alpine.js filters.
     */
    public function exportFiltered(Request $request)
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'in:all,active,inactive',
            ],

            'stock_filter' => [
                'nullable',
                'in:all,in_stock,low_stock,out_of_stock',
            ],
        ]);

        return Excel::download(
            new ProductsExport(
                $validated['search'] ?? null,
                $validated['status'] ?? 'all',
                $validated['stock_filter'] ?? 'all'
            ),
            'filtered-products.xlsx'
        );
    }
}