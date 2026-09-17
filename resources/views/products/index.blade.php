<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Alpine Product Manager</title>

    <script
        src="https://unpkg.com/alpinejs"
        defer>
    </script>

    <script src="https://cdn.tailwindcss.com"></script>

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

</head>


<body class="bg-gray-100 min-h-screen p-6 md:p-10">


<div
    class="max-w-7xl mx-auto"
    x-data="productApp()"
    x-init="fetchProducts()"
>


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                Alpine Product Manager
            </h1>

            <p class="text-gray-500 mt-1">
                Laravel 12 + Alpine.js + Excel Import/Export
            </p>

        </div>


        <button
            @click="openModal()"
            class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow"
        >
            + Add Product
        </button>

    </div>



    <!-- =========================================================
         MESSAGE
    ========================================================== -->

    <div
        x-show="message"
        x-transition
        :class="messageType === 'success'
            ? 'bg-green-100 text-green-700 border-green-300'
            : 'bg-red-100 text-red-700 border-red-300'"
        class="mb-6 px-4 py-3 rounded-lg border"
        x-text="message"
    >
    </div>



    <!-- =========================================================
         IMPORT SUMMARY
    ========================================================== -->

    <div
        x-show="importSummary.show"
        x-transition
        class="bg-white rounded-lg shadow p-5 mb-6 border"
    >

        <div class="flex items-center justify-between mb-4">

            <div>

                <h2 class="text-xl font-bold text-gray-800">
                    📊 Import Summary
                </h2>

                <p
                    class="text-sm text-gray-500 mt-1"
                    x-text="'Mode: ' + importSummary.modeLabel"
                ></p>

            </div>


            <button
                @click="importSummary.show = false"
                class="text-gray-500 hover:text-gray-800 text-xl"
            >
                ×
            </button>

        </div>


        <!-- SUMMARY CARDS -->

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">


            <!-- CREATED -->

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">

                <p class="text-sm text-green-600">
                    Created
                </p>

                <p
                    class="text-3xl font-bold text-green-700"
                    x-text="importSummary.created"
                ></p>

                <p class="text-sm text-gray-500">
                    new products
                </p>

            </div>


            <!-- UPDATED -->

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">

                <p class="text-sm text-blue-600">
                    Updated
                </p>

                <p
                    class="text-3xl font-bold text-blue-700"
                    x-text="importSummary.updated"
                ></p>

                <p class="text-sm text-gray-500">
                    existing products
                </p>

            </div>


            <!-- SKIPPED -->

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">

                <p class="text-sm text-red-600">
                    Skipped
                </p>

                <p
                    class="text-3xl font-bold text-red-700"
                    x-text="importSummary.skipped"
                ></p>

                <p class="text-sm text-gray-500">
                    invalid rows
                </p>

            </div>


            <!-- TOTAL -->

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">

                <p class="text-sm text-purple-600">
                    Total
                </p>

                <p
                    class="text-3xl font-bold text-purple-700"
                    x-text="importSummary.total"
                ></p>

                <p class="text-sm text-gray-500">
                    processed rows
                </p>

            </div>

        </div>



        <!-- =====================================================
             IMPORT ACTIVITY
        ====================================================== -->

        <div
            x-show="importSummary.activities.length > 0"
            class="mt-6"
        >

            <h3 class="font-semibold text-gray-800 mb-3">
                📋 Import Activity
            </h3>


            <div class="overflow-x-auto">

                <table class="w-full text-sm border">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="border p-2 text-left">
                                Row
                            </th>

                            <th class="border p-2 text-left">
                                Product
                            </th>

                            <th class="border p-2 text-left">
                                Action
                            </th>

                            <th class="border p-2 text-left">
                                Details
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <template
                            x-for="activity in importSummary.activities"
                            :key="activity.row + '-' + activity.name"
                        >

                            <tr>

                                <td
                                    class="border p-2"
                                    x-text="activity.row"
                                ></td>


                                <td
                                    class="border p-2 font-medium"
                                    x-text="activity.name"
                                ></td>


                                <td class="border p-2">

                                    <span
                                        class="px-2 py-1 rounded text-xs font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-700': activity.action === 'Created',
                                            'bg-blue-100 text-blue-700': activity.action === 'Updated',
                                            'bg-red-100 text-red-700': activity.action === 'Skipped'
                                        }"
                                        x-text="activity.action"
                                    ></span>

                                </td>


                                <td
                                    class="border p-2 text-gray-600"
                                    x-text="activity.message"
                                ></td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

        </div>



        <!-- =====================================================
             VALIDATION ERRORS
        ====================================================== -->

        <div
            x-show="importSummary.errors.length > 0"
            class="mt-6"
        >

            <h3 class="font-semibold text-red-600 mb-3">
                ⚠️ Validation Errors
            </h3>


            <div class="overflow-x-auto">

                <table class="w-full text-sm border">

                    <thead class="bg-red-50">

                        <tr>

                            <th class="border p-2 text-left">
                                Row
                            </th>

                            <th class="border p-2 text-left">
                                Product
                            </th>

                            <th class="border p-2 text-left">
                                Error
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <template
                            x-for="error in importSummary.errors"
                            :key="error.row"
                        >

                            <tr>

                                <td
                                    class="border p-2"
                                    x-text="error.row"
                                ></td>


                                <td
                                    class="border p-2"
                                    x-text="error.name"
                                ></td>


                                <td class="border p-2 text-red-600">

                                    <template
                                        x-for="item in error.errors"
                                        :key="item"
                                    >

                                        <div x-text="item"></div>

                                    </template>

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <!-- =========================================================
         SEARCH AND FILTERS
    ========================================================== -->

    <div class="bg-white rounded-lg shadow p-5 mb-6">

        <div class="flex items-center justify-between mb-4">

            <h2 class="text-xl font-bold text-gray-800">
                🔎 Search & Filter Products
            </h2>


            <button
                @click="clearFilters()"
                class="text-sm text-blue-600 hover:underline"
            >
                Clear Filters
            </button>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


            <!-- SEARCH -->

            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Search Product
                </label>

                <input
                    type="text"
                    x-model="search"
                    placeholder="Search by name or status..."
                    class="border rounded-lg p-2 w-full"
                >

            </div>


            <!-- STATUS -->

            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>

                <select
                    x-model="statusFilter"
                    class="border rounded-lg p-2 w-full"
                >

                    <option value="all">
                        All Status
                    </option>

                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>

            </div>


            <!-- STOCK -->

            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Stock
                </label>

                <select
                    x-model="stockFilter"
                    class="border rounded-lg p-2 w-full"
                >

                    <option value="all">
                        All Stock
                    </option>

                    <option value="in_stock">
                        In Stock
                    </option>

                    <option value="low_stock">
                        Low Stock (1-10)
                    </option>

                    <option value="out_of_stock">
                        Out of Stock
                    </option>

                </select>

            </div>

        </div>


        <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between">

            <p class="text-sm text-gray-600">

                Showing

                <span
                    class="font-bold"
                    x-text="filteredProducts.length"
                ></span>

                of

                <span
                    class="font-bold"
                    x-text="products.length"
                ></span>

                products

            </p>


            <div class="flex gap-2 mt-3 md:mt-0">

                <button
                    @click="exportFiltered()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg"
                >
                    📤 Export Filtered
                </button>


                <button
                    @click="exportProducts()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                >
                    📥 Export All
                </button>

            </div>

        </div>

    </div>



    <!-- =========================================================
         PRODUCT TABLE
    ========================================================== -->

    <div class="bg-white rounded-lg shadow overflow-hidden">

        <div class="p-4 border-b">

            <h2 class="text-xl font-bold text-gray-800">
                📋 Product List
            </h2>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-200">

                    <tr>

                        <th class="p-3 text-left">
                            ID
                        </th>

                        <th class="p-3 text-left">
                            Name
                        </th>

                        <th class="p-3 text-left">
                            Price
                        </th>

                        <th class="p-3 text-left">
                            Stock
                        </th>

                        <th class="p-3 text-left">
                            Status
                        </th>

                        <th class="p-3 text-left">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <template
                        x-for="product in filteredProducts"
                        :key="product.id"
                    >

                        <tr class="border-t hover:bg-gray-50">

                            <td
                                class="p-3"
                                x-text="product.id"
                            ></td>


                            <td
                                class="p-3 font-medium"
                                x-text="product.name"
                            ></td>


                            <td class="p-3">

                                ₹
                                <span x-text="product.price"></span>

                            </td>


                            <td class="p-3">

                                <span
                                    x-text="product.stock"
                                    :class="{
                                        'text-red-600 font-bold': product.stock == 0,
                                        'text-yellow-600 font-bold': product.stock > 0 && product.stock <= 10,
                                        'text-green-600 font-bold': product.stock > 10
                                    }"
                                ></span>

                            </td>


                            <td class="p-3">

                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                    :class="product.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'"
                                    x-text="product.status"
                                ></span>

                            </td>


                            <td class="p-3">

                                <div class="flex gap-2">

                                    <button
                                        @click="editProduct(product)"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded"
                                    >
                                        Edit
                                    </button>


                                    <button
                                        @click="deleteProduct(product.id)"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </template>


                    <template
                        x-if="filteredProducts.length === 0"
                    >

                        <tr>

                            <td
                                colspan="6"
                                class="p-8 text-center text-gray-500"
                            >
                                No products found.
                            </td>

                        </tr>

                    </template>

                </tbody>

            </table>

        </div>

    </div>



    <!-- =========================================================
         EXCEL IMPORT / EXPORT
    ========================================================== -->

    <div class="mt-6 bg-white p-5 rounded-lg shadow">

        <h2 class="text-xl font-bold text-gray-800 mb-4">
            📊 Excel Import / Export
        </h2>


        <!-- IMPORT MODE -->

        <div class="mb-5">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Excel Import Mode
            </label>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                <!-- ADD NEW -->

                <label
                    class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                    :class="importMode === 'add_new'
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300'"
                >

                    <div class="flex items-start gap-3">

                        <input
                            type="radio"
                            value="add_new"
                            x-model="importMode"
                            class="mt-1"
                        >


                        <div>

                            <p class="font-semibold text-gray-800">
                                ➕ Add New Products
                            </p>

                            <p class="text-sm text-gray-500 mt-1">
                                Every valid Excel row is added as a new product.
                            </p>

                        </div>

                    </div>

                </label>


                <!-- UPDATE EXISTING -->

                <label
                    class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                    :class="importMode === 'update_existing'
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300'"
                >

                    <div class="flex items-start gap-3">

                        <input
                            type="radio"
                            value="update_existing"
                            x-model="importMode"
                            class="mt-1"
                        >


                        <div>

                            <p class="font-semibold text-gray-800">
                                🔄 Update Existing Products
                            </p>

                            <p class="text-sm text-gray-500 mt-1">
                                Match by product name. Existing products are updated; new products are created.
                            </p>

                        </div>

                    </div>

                </label>

            </div>

        </div>


        <!-- FILE IMPORT -->

        <div class="flex flex-col md:flex-row gap-3">

            <input
                type="file"
                accept=".xlsx,.xls,.csv"
                @change="importFile($event)"
                class="border rounded-lg p-2"
            >


            <button
                @click="exportProducts()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
            >
                📥 Export All Products
            </button>

        </div>


        <!-- MODE INFORMATION -->

        <div class="mt-4">

            <template x-if="importMode === 'add_new'">

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700">

                    <strong>➕ Add New Mode:</strong>

                    Every valid Excel row creates a new product.

                </div>

            </template>


            <template x-if="importMode === 'update_existing'">

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-sm text-purple-700">

                    <strong>🔄 Update Existing Mode:</strong>

                    Products are matched using their name.
                    Existing products are updated and missing products are created.

                </div>

            </template>

        </div>


        <p class="text-sm text-gray-500 mt-3">

            Excel headers must be:

            <strong>
                name, price, stock, status
            </strong>

        </p>

    </div>



    <!-- =========================================================
         PRODUCT MODAL
    ========================================================== -->

    <div
        x-show="showModal"
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
    >

        <div
            @click.outside="showModal = false"
            class="bg-white p-6 rounded-xl w-full max-w-md shadow-xl"
        >

            <h2
                class="text-xl font-bold mb-5"
                x-text="form.id ? 'Edit Product' : 'Add Product'"
            ></h2>


            <label class="block text-sm font-medium mb-1">
                Product Name
            </label>

            <input
                x-model="form.name"
                placeholder="Product name"
                class="border p-2 w-full mb-3 rounded"
            >


            <label class="block text-sm font-medium mb-1">
                Price
            </label>

            <input
                x-model="form.price"
                type="number"
                min="0"
                step="0.01"
                placeholder="Price"
                class="border p-2 w-full mb-3 rounded"
            >


            <label class="block text-sm font-medium mb-1">
                Stock
            </label>

            <input
                x-model="form.stock"
                type="number"
                min="0"
                placeholder="Stock"
                class="border p-2 w-full mb-3 rounded"
            >


            <label class="block text-sm font-medium mb-1">
                Status
            </label>

            <select
                x-model="form.status"
                class="border p-2 w-full mb-5 rounded"
            >

                <option value="active">
                    Active
                </option>

                <option value="inactive">
                    Inactive
                </option>

            </select>


            <div class="flex justify-end gap-2">

                <button
                    @click="showModal = false"
                    class="border px-4 py-2 rounded-lg"
                >
                    Cancel
                </button>


                <button
                    @click="saveProduct()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                >
                    Save
                </button>

            </div>

        </div>

    </div>

</div>



<script>

function productApp() {

    return {

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        products: [],

        showModal: false,


        /*
        |--------------------------------------------------------------------------
        | Search / Filters
        |--------------------------------------------------------------------------
        */

        search: '',

        statusFilter: 'all',

        stockFilter: 'all',


        /*
        |--------------------------------------------------------------------------
        | Import Mode
        |--------------------------------------------------------------------------
        */

        importMode: 'add_new',


        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        */

        message: '',

        messageType: 'success',


        /*
        |--------------------------------------------------------------------------
        | Import Summary
        |--------------------------------------------------------------------------
        */

        importSummary: {

            show: false,

            mode: '',

            modeLabel: '',

            created: 0,

            updated: 0,

            imported: 0,

            skipped: 0,

            total: 0,

            errors: [],

            activities: []

        },


        /*
        |--------------------------------------------------------------------------
        | Product Form
        |--------------------------------------------------------------------------
        */

        form: {

            id: null,

            name: '',

            price: '',

            stock: '',

            status: 'active'

        },


        /*
        |--------------------------------------------------------------------------
        | Filtered Products
        |--------------------------------------------------------------------------
        */

        get filteredProducts() {

            return this.products.filter(product => {

                const searchText =
                    this.search
                        .toLowerCase()
                        .trim();


                const matchesSearch =
                    searchText === ''
                    ||
                    product.name
                        .toLowerCase()
                        .includes(searchText)
                    ||
                    product.status
                        .toLowerCase()
                        .includes(searchText);


                const matchesStatus =
                    this.statusFilter === 'all'
                    ||
                    product.status === this.statusFilter;


                let matchesStock = true;


                if (
                    this.stockFilter === 'in_stock'
                ) {

                    matchesStock =
                        Number(product.stock) > 0;

                }


                if (
                    this.stockFilter === 'low_stock'
                ) {

                    matchesStock =
                        Number(product.stock) > 0
                        &&
                        Number(product.stock) <= 10;

                }


                if (
                    this.stockFilter === 'out_of_stock'
                ) {

                    matchesStock =
                        Number(product.stock) === 0;

                }


                return (
                    matchesSearch
                    &&
                    matchesStatus
                    &&
                    matchesStock
                );

            });

        },


        /*
        |--------------------------------------------------------------------------
        | Show Message
        |--------------------------------------------------------------------------
        */

        showMessage(
            text,
            type = 'success'
        ) {

            this.message = text;

            this.messageType = type;


            setTimeout(() => {

                this.message = '';

            }, 4000);

        },


        /*
        |--------------------------------------------------------------------------
        | Fetch Products
        |--------------------------------------------------------------------------
        */

        fetchProducts() {

            fetch('/api/products')

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Unable to load products.'
                        );

                    }

                    return response.json();

                })

                .then(data => {

                    this.products = data;

                })

                .catch(error => {

                    console.error(error);

                    this.showMessage(
                        'Unable to load products.',
                        'error'
                    );

                });

        },


        /*
        |--------------------------------------------------------------------------
        | Clear Filters
        |--------------------------------------------------------------------------
        */

        clearFilters() {

            this.search = '';

            this.statusFilter = 'all';

            this.stockFilter = 'all';

        },


        /*
        |--------------------------------------------------------------------------
        | Open Modal
        |--------------------------------------------------------------------------
        */

        openModal() {

            this.form = {

                id: null,

                name: '',

                price: '',

                stock: '',

                status: 'active'

            };

            this.showModal = true;

        },


        /*
        |--------------------------------------------------------------------------
        | Edit Product
        |--------------------------------------------------------------------------
        */

        editProduct(product) {

            this.form = {

                id: product.id,

                name: product.name,

                price: product.price,

                stock: product.stock,

                status: product.status

            };

            this.showModal = true;

        },


        /*
        |--------------------------------------------------------------------------
        | Save Product
        |--------------------------------------------------------------------------
        */

        saveProduct() {

            if (!this.form.name.trim()) {

                this.showMessage(
                    'Product name is required.',
                    'error'
                );

                return;

            }


            if (
                this.form.price === ''
                ||
                Number(this.form.price) < 0
            ) {

                this.showMessage(
                    'Please enter a valid price.',
                    'error'
                );

                return;

            }


            if (
                this.form.stock === ''
                ||
                Number(this.form.stock) < 0
            ) {

                this.showMessage(
                    'Please enter a valid stock.',
                    'error'
                );

                return;

            }


            const productId =
                this.form.id;


            const url =
                '/api/products'
                +
                (
                    productId
                        ? '/' + productId
                        : ''
                );


            const method =
                productId
                    ? 'PUT'
                    : 'POST';


            fetch(url, {

                method: method,

                headers: {

                    'Content-Type':
                        'application/json',

                    'Accept':
                        'application/json',

                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            .content

                },

                body: JSON.stringify({

                    name:
                        this.form.name,

                    price:
                        this.form.price,

                    stock:
                        this.form.stock,

                    status:
                        this.form.status

                })

            })

            .then(async response => {

                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message
                        ||
                        'Validation failed.'
                    );

                }


                return data;

            })

            .then(() => {

                this.fetchProducts();

                this.showModal = false;


                this.showMessage(

                    productId
                        ? 'Product updated successfully!'
                        : 'Product added successfully!'

                );

            })

            .catch(error => {

                console.error(error);

                this.showMessage(
                    error.message
                    ||
                    'Something went wrong!',
                    'error'
                );

            });

        },


        /*
        |--------------------------------------------------------------------------
        | Delete Product
        |--------------------------------------------------------------------------
        */

        deleteProduct(id) {

            if (
                !confirm(
                    'Are you sure you want to delete this product?'
                )
            ) {

                return;

            }


            fetch(
                '/api/products/' + id,
                {

                    method: 'DELETE',

                    headers: {

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content

                    }

                }
            )

            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        'Unable to delete product.'
                    );

                }

                return response.json();

            })

            .then(() => {

                this.fetchProducts();

                this.showMessage(
                    'Product deleted successfully!'
                );

            })

            .catch(error => {

                console.error(error);

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /*
        |--------------------------------------------------------------------------
        | Excel Import
        |--------------------------------------------------------------------------
        */

        importFile(event) {

            const file =
                event.target.files[0];


            if (!file) {

                return;

            }


            const allowedExtensions = [
                'xlsx',
                'xls',
                'csv'
            ];


            const extension =
                file.name
                    .split('.')
                    .pop()
                    .toLowerCase();


            if (
                !allowedExtensions
                    .includes(extension)
            ) {

                this.showMessage(
                    'Please select an Excel or CSV file.',
                    'error'
                );

                event.target.value = '';

                return;

            }


            const formData =
                new FormData();


            formData.append(
                'file',
                file
            );


            formData.append(
                'import_mode',
                this.importMode
            );


            this.showMessage(
                this.importMode === 'update_existing'
                    ? 'Checking existing products and importing Excel data...'
                    : 'Importing new products from Excel...'
            );


            fetch(
                '/api/products/import',
                {

                    method: 'POST',

                    headers: {

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content

                    },

                    body: formData

                }
            )

            .then(async response => {

                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message
                        ||
                        'Import failed.'
                    );

                }


                return data;

            })

            .then(data => {


                /*
                |--------------------------------------------------------------------------
                | Update Summary
                |--------------------------------------------------------------------------
                */

                this.importSummary = {

                    show: true,

                    mode:
                        data.mode
                        ||
                        this.importMode,

                    modeLabel:
                        data.mode_label
                        ||
                        '',

                    created:
                        data.created
                        ||
                        0,

                    updated:
                        data.updated
                        ||
                        0,

                    imported:
                        data.imported
                        ||
                        0,

                    skipped:
                        data.skipped
                        ||
                        0,

                    total:
                        data.total
                        ||
                        0,

                    errors:
                        data.errors
                        ||
                        [],

                    activities:
                        data.activities
                        ||
                        []

                };


                /*
                |--------------------------------------------------------------------------
                | Refresh Product List
                |--------------------------------------------------------------------------
                */

                this.fetchProducts();


                /*
                |--------------------------------------------------------------------------
                | Success Message
                |--------------------------------------------------------------------------
                */

                this.showMessage(

                    `Import completed: ${data.created || 0} created, ${data.updated || 0} updated, ${data.skipped || 0} skipped.`

                );


                /*
                |--------------------------------------------------------------------------
                | Reset File Input
                |--------------------------------------------------------------------------
                */

                event.target.value = '';

            })

            .catch(error => {

                console.error(error);

                this.showMessage(
                    error.message
                    ||
                    'Excel import failed.',
                    'error'
                );

                event.target.value = '';

            });

        },


        /*
        |--------------------------------------------------------------------------
        | Export All
        |--------------------------------------------------------------------------
        */

        exportProducts() {

            this.showMessage(
                'Export started! File downloading...'
            );


            setTimeout(() => {

                window.location.href =
                    '/api/products/export';

            }, 500);

        },


        /*
        |--------------------------------------------------------------------------
        | Export Filtered
        |--------------------------------------------------------------------------
        */

        exportFiltered() {

            if (
                this.filteredProducts.length === 0
            ) {

                this.showMessage(
                    'There are no products to export with the current filters.',
                    'error'
                );

                return;

            }


            const params =
                new URLSearchParams({

                    search:
                        this.search,

                    status:
                        this.statusFilter,

                    stock_filter:
                        this.stockFilter

                });


            this.showMessage(
                'Filtered export started! File downloading...'
            );


            setTimeout(() => {

                window.location.href =
                    '/api/products/export-filtered?'
                    +
                    params.toString();

            }, 500);

        }

    }

}

</script>


</body>

</html>