# PHP Laravel 12 Import Export Using Alpine.js

## Features: CRUD + Excel Import + Excel Export + Success Messages

---

## STEP 1 — Install Laravel Project

```bash
composer create-project laravel/laravel product-manager
php artisan serve
```

---

## STEP 2 — Database Configuration

Open `.env` and update:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=import_export
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in MySQL, then run:

```bash
php artisan migrate
```

---

## STEP 3 — Install Excel Package

```bash
composer require maatwebsite/excel
```

This package handles Excel import and export.

---

## STEP 4 — Create Product Model & Migration

```bash
php artisan make:model Product -m
```

### Update Migration

`database/migrations/xxxx_create_products_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run migration:

```bash
php artisan migrate
```

---

## STEP 5 — Product Model

`app/Models/Product.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'stock', 'status'];
}
```

---

## STEP 6 — Export Class

```bash
php artisan make:export ProductsExport --model=Product
```

`app/Exports/ProductsExport.php`

```php
<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::select('id','name','price','stock','status')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Price', 'Stock', 'Status'];
    }
}
```

---

## STEP 7 — Import Class

```bash
php artisan make:import ProductsImport --model=Product
```

`app/Imports/ProductsImport.php`

```php
<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'name'   => $row['name'],
            'price'  => $row['price'],
            'stock'  => $row['stock'],
            'status' => $row['status'] ?? 'active',
        ]);
    }
}
```

Excel column headers must be:

```
name | price | stock | status
```

---

## STEP 8 — Product Controller

```bash
php artisan make:controller ProductController
```

`app/Http/Controllers/ProductController.php`

```php
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
        return Product::create($request->validate([ // Validate and create new product
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required'
        ]));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->validate([ // Validate and update existing product
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
```

---

## STEP 9 — Routes

`routes/web.php`

```php
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
```

---

## STEP 10 — Alpine.js Frontend

Create file:
`resources/views/products/index.blade.php`

(Full Alpine CRUD + Import + Export UI code continues exactly as provided in the project — unchanged.)

---

## STEP 11 — Run Project

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000
```

---

# FEATURE OUTPUTS

### 1. CREATE PRODUCT

<img width="1239" height="534" alt="Screenshot 2026-01-30 124958" src="https://github.com/user-attachments/assets/91da27b1-0e6f-4d77-a28e-e1bde9c9913c" />


### 2. EDIT PRODUCT

<img width="1239" height="505" alt="Screenshot 2026-01-30 123619" src="https://github.com/user-attachments/assets/55c8c33c-e563-43cd-bd7a-6a8f6ddc52a2" />


### 3. DELETE PRODUCT

<img width="1235" height="470" alt="Screenshot 2026-01-30 123647" src="https://github.com/user-attachments/assets/cf755c59-21ff-4778-a547-d58e852d271d" />


### 4. IMPORT PRODUCTS (Excel)

<img width="319" height="84" alt="Screenshot 2026-01-30 124513" src="https://github.com/user-attachments/assets/da643f30-ca22-4982-9e79-ed0d48bac3a8" />

<img width="1251" height="556" alt="Screenshot 2026-01-30 123550" src="https://github.com/user-attachments/assets/ac0d7764-1641-4cb1-aef9-338fd816d052" />


### 5. EXPORT PRODUCTS

<img width="1535" height="482" alt="Screenshot 2026-01-30 124343" src="https://github.com/user-attachments/assets/57758321-8f2f-41f1-8810-e783b0aca1a5" />




---

# FINAL RESULT

| Feature  | Output                            |
| -------- | --------------------------------- |
| Create   | Product appears + success message |
| Edit     | Data updates instantly            |
| Delete   | Row removed instantly             |
| Import   | Excel data added to DB            |
| Export   | Excel file downloads              |
| Messages | Visible for every action          |

---


