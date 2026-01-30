<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index'); // Load main UI page
    }

    public function list()
    {
        return Product::latest()->get(); // Return all products as JSON
    }

    public function store(Request $request)
    {
        // Validate and create new product
        return Product::create($request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required'
        ]));
    }

    public function update(Request $request, Product $product)
    {
        // Validate and update existing product
        $product->update($request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required'
        ]));

        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete(); // Delete product
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        Excel::import(new ProductsImport, $request->file('file')); // Import Excel data
        return response()->json(['success' => true]);
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx'); // Download Excel file
    }
}


