<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function list(): JsonResponse
    {
        return response()->json(
            Product::latest()->get()
        );
    }

    /**
     * Product statistics.
     */
    public function statistics(): JsonResponse
    {
        $total = Product::count();
        $active = Product::where('status', 'active')->count();
        $inactive = Product::where('status', 'inactive')->count();
        $totalStock = (int) Product::sum('stock');
        $inventoryValue = (float) (Product::selectRaw('COALESCE(SUM(price * stock), 0) as total')->value('total') ?? 0);
        $outOfStock = Product::where('stock', 0)->count();
        $lowStock = Product::whereBetween('stock', [1, 10])->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'total_stock' => $totalStock,
            'inventory_value' => round($inventoryValue, 2),
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
        ]);
    }

    /**
     * Create product.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $product = Product::create($validated);

        return response()->json($product);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): JsonResponse
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
    public function duplicate(Product $product): JsonResponse
    {
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' - Copy';
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
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $deleted = Product::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => $deleted . ' product(s) deleted successfully.',
        ]);
    }

    /**
     * Bulk status update.
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $updated = Product::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'status' => $validated['status'],
            'message' => $updated . ' product(s) updated successfully.',
        ]);
    }

    /**
     * Standard Excel/CSV File Import
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'import_mode' => ['required', 'in:add_new,update_existing'],
        ]);

        try {
            $import = new ProductsImport($request->input('import_mode'));
            Excel::import($import, $request->file('file'));

            $modeLabel = $request->input('import_mode') === 'update_existing' ? 'Update Existing' : 'Add New';

            return response()->json([
                'success' => true,
                'message' => 'Excel import completed successfully.',
                'mode' => $request->input('import_mode'),
                'mode_label' => $modeLabel,
                'created' => $import->createdCount,
                'updated' => $import->updatedCount,
                'imported' => $import->createdCount + $import->updatedCount,
                'skipped' => $import->skippedCount,
                'total' => $import->createdCount + $import->updatedCount + $import->skippedCount,
                'errors' => $import->errors,
                'activities' => $import->activities,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to import the file: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Direct Pre-flight Mapped Data Import (From Interactive Grid & Error Fixer)
     */
    public function importDirect(Request $request): JsonResponse
    {
        $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.stock' => ['required', 'integer', 'min:0'],
            'rows.*.status' => ['required', 'in:active,inactive'],
            'import_mode' => ['required', 'in:add_new,update_existing'],
        ]);

        $rows = $request->input('rows');
        $mode = $request->input('import_mode');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $activities = [];

        DB::transaction(function () use ($rows, $mode, &$created, &$updated, &$skipped, &$errors, &$activities) {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 1;
                $cleanName = trim($row['name']);
                $cleanPrice = (float) $row['price'];
                $cleanStock = (int) $row['stock'];
                $cleanStatus = in_array(strtolower(trim($row['status'])), ['active', 'inactive']) ? strtolower(trim($row['status'])) : 'active';

                if ($mode === 'add_new') {
                    $product = Product::create([
                        'name' => $cleanName,
                        'price' => $cleanPrice,
                        'stock' => $cleanStock,
                        'status' => $cleanStatus,
                    ]);
                    $created++;
                    $activities[] = [
                        'row' => $rowNum,
                        'name' => $product->name,
                        'action' => 'Created',
                        'message' => 'Created via interactive grid import.',
                    ];
                } else {
                    // Update existing by name match
                    $existing = Product::where('name', $cleanName)->first();
                    if ($existing) {
                        $existing->update([
                            'price' => $cleanPrice,
                            'stock' => $cleanStock,
                            'status' => $cleanStatus,
                        ]);
                        $updated++;
                        $activities[] = [
                            'row' => $rowNum,
                            'name' => $existing->name,
                            'action' => 'Updated',
                            'message' => 'Existing product updated via interactive grid.',
                        ];
                    } else {
                        $product = Product::create([
                            'name' => $cleanName,
                            'price' => $cleanPrice,
                            'stock' => $cleanStock,
                            'status' => $cleanStatus,
                        ]);
                        $created++;
                        $activities[] = [
                            'row' => $rowNum,
                            'name' => $product->name,
                            'action' => 'Created',
                            'message' => 'New product created (not found in database).',
                        ];
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Interactive grid import completed: {$created} created, {$updated} updated.",
            'mode' => $mode,
            'mode_label' => $mode === 'update_existing' ? 'Update Existing' : 'Add New',
            'created' => $created,
            'updated' => $updated,
            'imported' => $created + $updated,
            'skipped' => $skipped,
            'total' => count($rows),
            'errors' => $errors,
            'activities' => $activities,
        ]);
    }

    /**
     * Standard Excel Export
     */
    public function export()
    {
        return Excel::download(new ProductsExport(), 'products.xlsx');
    }

    /**
     * Filtered Excel Export
     */
    public function exportFiltered(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'stock_filter' => ['nullable', 'in:all,in_stock,low_stock,out_of_stock'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
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

    /**
     * Multi-Format Custom Export Builder (Excel, CSV, JSON, Printable PDF/HTML Report)
     */
    public function exportCustom(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:xlsx,csv,json,pdf_html'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string', 'in:id,name,price,stock,status,inventory_value,created_at,updated_at'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'stock_filter' => ['nullable', 'in:all,in_stock,low_stock,out_of_stock'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $columns = !empty($validated['columns']) ? $validated['columns'] : ['id', 'name', 'price', 'stock', 'status', 'created_at'];
        $format = $validated['format'];

        $export = new ProductsExport(
            $validated['search'] ?? null,
            $validated['status'] ?? 'all',
            $validated['stock_filter'] ?? 'all',
            $validated['min_price'] ?? null,
            $validated['max_price'] ?? null,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $columns
        );

        $timestamp = now()->format('Y_m_d_His');

        // 1. EXCEL (.xlsx)
        if ($format === 'xlsx') {
            return Excel::download($export, "products_custom_{$timestamp}.xlsx");
        }

        // 2. CSV (.csv)
        if ($format === 'csv') {
            return Excel::download($export, "products_custom_{$timestamp}.csv", \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);
        }

        // 3. JSON (.json)
        if ($format === 'json') {
            $query = $export->query();
            $products = $query->get();

            $data = $products->map(function ($product) use ($columns) {
                $item = [];
                foreach ($columns as $col) {
                    if ($col === 'inventory_value') {
                        $item[$col] = round((float) $product->price * (int) $product->stock, 2);
                    } elseif ($col === 'created_at' || $col === 'updated_at') {
                        $item[$col] = $product->{$col}?->format('Y-m-d H:i:s');
                    } else {
                        $item[$col] = $product->{$col};
                    }
                }
                return $item;
            });

            $fileName = "products_custom_{$timestamp}.json";
            return response()->streamDownload(function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, $fileName, ['Content-Type' => 'application/json']);
        }

        // 4. PRINTABLE PDF / HTML REPORT (.html with auto print & PDF generation)
        if ($format === 'pdf_html') {
            $query = $export->query();
            $products = $query->get();
            $headings = $export->headings();

            return response()->view('products.pdf_report', [
                'products' => $products,
                'columns' => $columns,
                'headings' => $headings,
                'totalCount' => $products->count(),
                'totalStock' => $products->sum('stock'),
                'totalValue' => $products->sum(fn($p) => (float) $p->price * (int) $p->stock),
                'generatedAt' => now()->format('F d, Y H:i:s'),
            ]);
        }

        return redirect()->back();
    }
}