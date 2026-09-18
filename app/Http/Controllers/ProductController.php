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
     * Display product management page.
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
     * Product statistics.
     */
    public function statistics()
    {
        $total = Product::count();

        $active = Product::where(
            'status',
            'active'
        )->count();

        $inactive = Product::where(
            'status',
            'inactive'
        )->count();

        $totalStock = Product::sum('stock');

        $inventoryValue = Product::selectRaw(
            'COALESCE(SUM(price * stock), 0) as total'
        )->value('total');

        $outOfStock = Product::where(
            'stock',
            0
        )->count();

        $lowStock = Product::whereBetween(
            'stock',
            [1, 10]
        )->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'total_stock' => $totalStock,
            'inventory_value' => $inventoryValue,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
        ]);
    }

    /**
     * Create product.
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
     * Update product.
     */
    public function update(
        Request $request,
        Product $product
    ) {
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
     * Delete product.
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
     * Duplicate product.
     */
    public function duplicate(Product $product)
    {
        $newProduct = $product->replicate();

        $newProduct->name =
            $product->name . ' - Copy';

        $newProduct->save();

        return response()->json([
            'success' => true,
            'message' => 'Product duplicated successfully.',
            'product' => $newProduct,
        ]);
    }

    /**
     * Bulk delete products.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'exists:products,id',
            ],
        ]);

        $deleted = Product::whereIn(
            'id',
            $validated['ids']
        )->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' =>
                $deleted . ' product(s) deleted successfully.',
        ]);
    }

    /**
     * Bulk status update.
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'exists:products,id',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $updated = Product::whereIn(
            'id',
            $validated['ids']
        )->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'status' => $validated['status'],
            'message' =>
                $updated . ' product(s) updated successfully.',
        ]);
    }

    /**
     * Import products.
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
            $import = new ProductsImport(
                $request->input('import_mode')
            );

            Excel::import(
                $import,
                $request->file('file')
            );

            $modeLabel =
                $request->input('import_mode') === 'update_existing'
                    ? 'Update Existing'
                    : 'Add New';

            return response()->json([
                'success' => true,

                'message' =>
                    'Excel import completed successfully.',

                'mode' =>
                    $request->input('import_mode'),

                'mode_label' =>
                    $modeLabel,

                'created' =>
                    $import->createdCount,

                'updated' =>
                    $import->updatedCount,

                'imported' =>
                    $import->createdCount
                    +
                    $import->updatedCount,

                'skipped' =>
                    $import->skippedCount,

                'total' =>
                    $import->createdCount
                    +
                    $import->updatedCount
                    +
                    $import->skippedCount,

                'errors' =>
                    $import->errors,

                'activities' =>
                    $import->activities,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to import the Excel file.',
                'error' =>
                    $e->getMessage(),
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
     * Export filtered products.
     */
    public function exportFiltered(
        Request $request
    ) {
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

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
            ],
        ]);

        return Excel::download(
            new ProductsExport(
                $validated['search'] ?? null,
                $validated['status'] ?? 'all',
                $validated['stock_filter'] ?? 'all',
                $validated['min_price'] ?? null,
                $validated['max_price'] ?? null,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            ),
            'filtered-products.xlsx'
        );
    }
}