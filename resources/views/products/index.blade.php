<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>Laravel 12 Alpine Product Manager</title>

    <script
        src="https://cdn.tailwindcss.com"
    ></script>

    <script
        src="https://unpkg.com/alpinejs"
        defer
    ></script>

</head>

<body class="bg-gray-100 min-h-screen p-6">

<div
    class="max-w-7xl mx-auto"
    x-data="productApp()"
    x-init="init()"
>

    <!-- HEADER -->

    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                Alpine Product Manager
            </h1>

            <p class="text-gray-500">
                Laravel 12 + Alpine.js + Excel Import/Export
            </p>

        </div>

        <button
            @click="openModal()"
            class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
        >
            + Add Product
        </button>

    </div>


    <!-- MESSAGE -->

    <div
        x-show="message"
        x-transition
        :class="
            messageType === 'success'
                ? 'bg-green-100 text-green-700 border-green-300'
                : 'bg-red-100 text-red-700 border-red-300'
        "
        class="mb-6 px-4 py-3 rounded-lg border"
        x-text="message"
    ></div>


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Total Products
            </p>

            <p
                class="text-3xl font-bold text-gray-800"
                x-text="statistics.total"
            ></p>

        </div>


        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Active Products
            </p>

            <p
                class="text-3xl font-bold text-green-600"
                x-text="statistics.active"
            ></p>

        </div>


        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Inactive Products
            </p>

            <p
                class="text-3xl font-bold text-red-600"
                x-text="statistics.inactive"
            ></p>

        </div>


        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Inventory Value
            </p>

            <p
                class="text-3xl font-bold text-purple-600"
                x-text="'₹ ' + Number(statistics.inventory_value).toLocaleString()"
            ></p>

        </div>

    </div>


    <!-- SECOND STATISTICS ROW -->

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-4">

            <span class="text-gray-500">
                Total Stock
            </span>

            <strong
                class="float-right"
                x-text="statistics.total_stock"
            ></strong>

        </div>


        <div class="bg-white rounded-xl shadow p-4">

            <span class="text-gray-500">
                Low Stock
            </span>

            <strong
                class="float-right text-yellow-600"
                x-text="statistics.low_stock"
            ></strong>

        </div>


        <div class="bg-white rounded-xl shadow p-4">

            <span class="text-gray-500">
                Out of Stock
            </span>

            <strong
                class="float-right text-red-600"
                x-text="statistics.out_of_stock"
            ></strong>

        </div>

    </div>


    <!-- =====================================================
         SEARCH + FILTERS
    ====================================================== -->

    <div class="bg-white rounded-xl shadow p-5 mb-6">

        <div class="flex justify-between items-center mb-4">

            <h2 class="text-xl font-bold">
                🔎 Search & Advanced Filters
            </h2>

            <button
                @click="clearFilters()"
                class="text-blue-600 hover:underline"
            >
                Clear Filters
            </button>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

            <!-- SEARCH -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    Search
                </label>

                <input
                    x-model="search"
                    type="text"
                    placeholder="Name or status..."
                    class="border rounded-lg p-2 w-full"
                >

            </div>


            <!-- STATUS -->

            <div>

                <label class="block text-sm font-medium mb-1">
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

                <label class="block text-sm font-medium mb-1">
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
                        Low Stock
                    </option>

                    <option value="out_of_stock">
                        Out of Stock
                    </option>

                </select>

            </div>


            <!-- SORT -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    Sort By
                </label>

                <select
                    x-model="sortBy"
                    class="border rounded-lg p-2 w-full"
                >

                    <option value="created_at">
                        Newest
                    </option>

                    <option value="id">
                        ID
                    </option>

                    <option value="name">
                        Name
                    </option>

                    <option value="price">
                        Price
                    </option>

                    <option value="stock">
                        Stock
                    </option>

                </select>

            </div>


            <!-- MIN PRICE -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    Minimum Price
                </label>

                <input
                    x-model.number="minPrice"
                    type="number"
                    min="0"
                    placeholder="0"
                    class="border rounded-lg p-2 w-full"
                >

            </div>


            <!-- MAX PRICE -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    Maximum Price
                </label>

                <input
                    x-model.number="maxPrice"
                    type="number"
                    min="0"
                    placeholder="999999"
                    class="border rounded-lg p-2 w-full"
                >

            </div>


            <!-- START DATE -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    From Date
                </label>

                <input
                    x-model="startDate"
                    type="date"
                    class="border rounded-lg p-2 w-full"
                >

            </div>


            <!-- END DATE -->

            <div>

                <label class="block text-sm font-medium mb-1">
                    To Date
                </label>

                <input
                    x-model="endDate"
                    type="date"
                    class="border rounded-lg p-2 w-full"
                >

            </div>

        </div>


        <!-- FILTER INFO -->

        <div class="mt-5 flex flex-col md:flex-row md:justify-between gap-3">

            <p class="text-sm text-gray-600">

                Showing

                <strong
                    x-text="filteredProducts.length"
                ></strong>

                of

                <strong
                    x-text="products.length"
                ></strong>

                products

            </p>


            <div class="flex gap-2">

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


    <!-- =====================================================
         BULK ACTION BAR
    ====================================================== -->

    <div
        x-show="selectedIds.length > 0"
        x-transition
        class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4"
    >

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

            <div>

                <strong
                    x-text="selectedIds.length"
                ></strong>

                product(s) selected.

            </div>


            <div class="flex flex-wrap gap-2">

                <button
                    @click="bulkStatus('active')"
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded"
                >
                    ✓ Set Active
                </button>


                <button
                    @click="bulkStatus('inactive')"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded"
                >
                    ⏸ Set Inactive
                </button>


                <button
                    @click="bulkDelete()"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded"
                >
                    🗑 Delete Selected
                </button>


                <button
                    @click="selectedIds = []"
                    class="border px-3 py-2 rounded"
                >
                    Clear Selection
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PRODUCT TABLE
    ====================================================== -->

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="p-4 border-b flex justify-between">

            <h2 class="text-xl font-bold">
                📋 Product List
            </h2>


            <span class="text-sm text-gray-500">

                Page

                <span
                    x-text="currentPage"
                ></span>

                /

                <span
                    x-text="totalPages"
                ></span>

            </span>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-200">

                    <tr>

                        <!-- SELECT ALL -->

                        <th class="p-3 text-left">

                            <input
                                type="checkbox"
                                @change="toggleSelectAll($event)"
                                :checked="pageProducts.length > 0 && pageProducts.every(p => selectedIds.includes(p.id))"
                            >

                        </th>


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
                            Created
                        </th>

                        <th class="p-3 text-left">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <template
                        x-for="product in pageProducts"
                        :key="product.id"
                    >

                        <tr class="border-t hover:bg-gray-50">

                            <!-- CHECKBOX -->

                            <td class="p-3">

                                <input
                                    type="checkbox"
                                    :value="product.id"
                                    :checked="selectedIds.includes(product.id)"
                                    @change="toggleSelect(product.id)"
                                >

                            </td>


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

                                <span
                                    x-text="Number(product.price).toLocaleString()"
                                ></span>

                            </td>


                            <td class="p-3">

                                <span
                                    x-text="product.stock"
                                    :class="{
                                        'text-red-600 font-bold': Number(product.stock) === 0,
                                        'text-yellow-600 font-bold': Number(product.stock) > 0 && Number(product.stock) <= 10,
                                        'text-green-600 font-bold': Number(product.stock) > 10
                                    }"
                                ></span>

                            </td>


                            <td class="p-3">

                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                    :class="
                                        product.status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    "
                                    x-text="product.status"
                                ></span>

                            </td>


                            <td
                                class="p-3 text-sm text-gray-500"
                                x-text="formatDate(product.created_at)"
                            ></td>


                            <td class="p-3">

                                <div class="flex flex-wrap gap-2">

                                    <button
                                        @click="editProduct(product)"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded"
                                    >
                                        Edit
                                    </button>


                                    <button
                                        @click="duplicateProduct(product.id)"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded"
                                    >
                                        Duplicate
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
                        x-if="pageProducts.length === 0"
                    >

                        <tr>

                            <td
                                colspan="8"
                                class="p-8 text-center text-gray-500"
                            >
                                No products found.
                            </td>

                        </tr>

                    </template>

                </tbody>

            </table>

        </div>


        <!-- PAGINATION -->

        <div class="p-4 border-t flex flex-wrap justify-center gap-2">

            <template
                x-for="page in paginationPages"
                :key="page"
            >

                <button
                    @click="goToPage(page)"
                    class="px-3 py-2 rounded border"
                    :class="
                        page === currentPage
                            ? 'bg-blue-600 text-white'
                            : 'bg-white hover:bg-gray-100'
                    "
                    x-text="page"
                ></button>

            </template>

        </div>

    </div>


    <!-- =====================================================
         IMPORT / EXPORT
    ====================================================== -->

    <div class="bg-white rounded-xl shadow p-5 mt-6">

        <h2 class="text-xl font-bold mb-5">
            📊 Excel Import / Export
        </h2>


        <div class="mb-5">

            <label class="block text-sm font-medium mb-2">
                Import Mode
            </label>


            <div class="grid md:grid-cols-2 gap-4">

                <label
                    class="border rounded-lg p-4 cursor-pointer"
                    :class="
                        importMode === 'add_new'
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300'
                    "
                >

                    <input
                        type="radio"
                        value="add_new"
                        x-model="importMode"
                    >

                    <strong class="ml-2">
                        ➕ Add New
                    </strong>

                    <p class="text-sm text-gray-500 mt-1">
                        Create every valid Excel row.
                    </p>

                </label>


                <label
                    class="border rounded-lg p-4 cursor-pointer"
                    :class="
                        importMode === 'update_existing'
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300'
                    "
                >

                    <input
                        type="radio"
                        value="update_existing"
                        x-model="importMode"
                    >

                    <strong class="ml-2">
                        🔄 Update Existing
                    </strong>

                    <p class="text-sm text-gray-500 mt-1">
                        Match products by name.
                    </p>

                </label>

            </div>

        </div>


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

    </div>


    <!-- =====================================================
         IMPORT SUMMARY
    ====================================================== -->

    <div
        x-show="importSummary.show"
        x-transition
        class="bg-white rounded-xl shadow p-5 mt-6"
    >

        <div class="flex justify-between mb-4">

            <h2 class="text-xl font-bold">
                📊 Import Summary
            </h2>

            <button
                @click="importSummary.show = false"
                class="text-xl"
            >
                ×
            </button>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-green-50 p-4 rounded-lg">

                <p class="text-sm text-green-600">
                    Created
                </p>

                <strong
                    class="text-3xl text-green-700"
                    x-text="importSummary.created"
                ></strong>

            </div>


            <div class="bg-blue-50 p-4 rounded-lg">

                <p class="text-sm text-blue-600">
                    Updated
                </p>

                <strong
                    class="text-3xl text-blue-700"
                    x-text="importSummary.updated"
                ></strong>

            </div>


            <div class="bg-red-50 p-4 rounded-lg">

                <p class="text-sm text-red-600">
                    Skipped
                </p>

                <strong
                    class="text-3xl text-red-700"
                    x-text="importSummary.skipped"
                ></strong>

            </div>


            <div class="bg-purple-50 p-4 rounded-lg">

                <p class="text-sm text-purple-600">
                    Total
                </p>

                <strong
                    class="text-3xl text-purple-700"
                    x-text="importSummary.total"
                ></strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MODAL
    ====================================================== -->

    <div
        x-show="showModal"
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    >

        <div
            @click.outside="showModal = false"
            class="bg-white p-6 rounded-xl w-full max-w-md"
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
                class="border p-2 w-full mb-3 rounded"
            >


            <label class="block text-sm font-medium mb-1">
                Stock
            </label>

            <input
                x-model="form.stock"
                type="number"
                min="0"
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

        products: [],

        search: '',

        statusFilter: 'all',

        stockFilter: 'all',

        minPrice: '',

        maxPrice: '',

        startDate: '',

        endDate: '',

        sortBy: 'created_at',

        sortDirection: 'desc',

        currentPage: 1,

        perPage: 5,

        selectedIds: [],

        showModal: false,

        importMode: 'add_new',

        message: '',

        messageType: 'success',

        statistics: {

            total: 0,

            active: 0,

            inactive: 0,

            total_stock: 0,

            inventory_value: 0,

            out_of_stock: 0,

            low_stock: 0

        },

        importSummary: {

            show: false,

            created: 0,

            updated: 0,

            imported: 0,

            skipped: 0,

            total: 0,

            errors: [],

            activities: []

        },

        form: {

            id: null,

            name: '',

            price: '',

            stock: '',

            status: 'active'

        },


        /* =========================================
           INITIALIZE
        ========================================= */

        init() {

            this.fetchProducts();

            this.fetchStatistics();

        },


        /* =========================================
           FILTERED PRODUCTS
        ========================================= */

        get filteredProducts() {

            let result = [...this.products];

            const searchText =
                this.search
                    .toLowerCase()
                    .trim();


            if (searchText !== '') {

                result = result.filter(product => {

                    return (
                        String(product.name)
                            .toLowerCase()
                            .includes(searchText)
                        ||
                        String(product.status)
                            .toLowerCase()
                            .includes(searchText)
                    );

                });

            }


            if (this.statusFilter !== 'all') {

                result = result.filter(
                    product =>
                        product.status === this.statusFilter
                );

            }


            if (this.stockFilter !== 'all') {

                if (this.stockFilter === 'in_stock') {

                    result = result.filter(
                        product =>
                            Number(product.stock) > 0
                    );

                }

                if (this.stockFilter === 'low_stock') {

                    result = result.filter(
                        product =>
                            Number(product.stock) > 0
                            &&
                            Number(product.stock) <= 10
                    );

                }

                if (this.stockFilter === 'out_of_stock') {

                    result = result.filter(
                        product =>
                            Number(product.stock) === 0
                    );

                }

            }


            if (this.minPrice !== '') {

                result = result.filter(
                    product =>
                        Number(product.price)
                        >=
                        Number(this.minPrice)
                );

            }


            if (this.maxPrice !== '') {

                result = result.filter(
                    product =>
                        Number(product.price)
                        <=
                        Number(this.maxPrice)
                );

            }


            if (this.startDate !== '') {

                result = result.filter(
                    product =>
                        String(product.created_at)
                            .substring(0, 10)
                        >=
                        this.startDate
                );

            }


            if (this.endDate !== '') {

                result = result.filter(
                    product =>
                        String(product.created_at)
                            .substring(0, 10)
                        <=
                        this.endDate
                );

            }


            result.sort((a, b) => {

                let valueA =
                    a[this.sortBy];

                let valueB =
                    b[this.sortBy];


                if (this.sortBy === 'name') {

                    valueA =
                        String(valueA).toLowerCase();

                    valueB =
                        String(valueB).toLowerCase();

                }


                if (
                    this.sortBy === 'price'
                    ||
                    this.sortBy === 'stock'
                    ||
                    this.sortBy === 'id'
                ) {

                    valueA = Number(valueA);

                    valueB = Number(valueB);

                }


                if (valueA < valueB) {

                    return this.sortDirection === 'asc'
                        ? -1
                        : 1;

                }


                if (valueA > valueB) {

                    return this.sortDirection === 'asc'
                        ? 1
                        : -1;

                }


                return 0;

            });


            return result;

        },


        /* =========================================
           PAGINATION
        ========================================= */

        get totalPages() {

            return Math.max(
                1,
                Math.ceil(
                    this.filteredProducts.length
                    /
                    this.perPage
                )
            );

        },


        get pageProducts() {

            if (
                this.currentPage
                >
                this.totalPages
            ) {

                this.currentPage =
                    this.totalPages;

            }


            const start =
                (
                    this.currentPage - 1
                )
                *
                this.perPage;


            return this.filteredProducts.slice(
                start,
                start + this.perPage
            );

        },


        get paginationPages() {

            return Array.from(
                {
                    length: this.totalPages
                },
                (_, index) => index + 1
            );

        },


        goToPage(page) {

            this.currentPage = page;

            this.selectedIds = [];

        },


        /* =========================================
           FETCH PRODUCTS
        ========================================= */

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


        /* =========================================
           FETCH STATISTICS
        ========================================= */

        fetchStatistics() {

            fetch('/api/products/statistics')

                .then(response =>
                    response.json()
                )

                .then(data => {

                    this.statistics = data;

                })

                .catch(error => {

                    console.error(error);

                });

        },


        /* =========================================
           FILTER RESET
        ========================================= */

        clearFilters() {

            this.search = '';

            this.statusFilter = 'all';

            this.stockFilter = 'all';

            this.minPrice = '';

            this.maxPrice = '';

            this.startDate = '';

            this.endDate = '';

            this.sortBy = 'created_at';

            this.sortDirection = 'desc';

            this.currentPage = 1;

        },


        /* =========================================
           SELECT PRODUCT
        ========================================= */

        toggleSelect(id) {

            if (
                this.selectedIds.includes(id)
            ) {

                this.selectedIds =
                    this.selectedIds.filter(
                        selectedId =>
                            selectedId !== id
                    );

            } else {

                this.selectedIds.push(id);

            }

        },


        /* =========================================
           SELECT ALL CURRENT PAGE
        ========================================= */

        toggleSelectAll(event) {

            if (event.target.checked) {

                this.pageProducts.forEach(
                    product => {

                        if (
                            !this.selectedIds
                                .includes(product.id)
                        ) {

                            this.selectedIds.push(
                                product.id
                            );

                        }

                    }
                );

            } else {

                const currentPageIds =
                    this.pageProducts.map(
                        product =>
                            product.id
                    );

                this.selectedIds =
                    this.selectedIds.filter(
                        id =>
                            !currentPageIds.includes(id)
                    );

            }

        },


        /* =========================================
           BULK DELETE
        ========================================= */

        bulkDelete() {

            if (
                this.selectedIds.length === 0
            ) {

                return;

            }


            if (
                !confirm(
                    `Delete ${this.selectedIds.length} selected product(s)?`
                )
            ) {

                return;

            }


            fetch(
                '/api/products/bulk-delete',
                {

                    method: 'POST',

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

                        ids:
                            this.selectedIds

                    })

                }
            )

            .then(async response => {

                const data =
                    await response.json();

                if (!response.ok) {

                    throw new Error(
                        data.message
                        ||
                        'Bulk delete failed.'
                    );

                }

                return data;

            })

            .then(data => {

                this.selectedIds = [];

                this.fetchProducts();

                this.fetchStatistics();

                this.showMessage(
                    data.message
                );

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /* =========================================
           BULK STATUS
        ========================================= */

        bulkStatus(status) {

            if (
                this.selectedIds.length === 0
            ) {

                return;

            }


            const label =
                status === 'active'
                    ? 'activate'
                    : 'deactivate';


            if (
                !confirm(
                    `${label} ${this.selectedIds.length} selected product(s)?`
                )
            ) {

                return;

            }


            fetch(
                '/api/products/bulk-status',
                {

                    method: 'POST',

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

                        ids:
                            this.selectedIds,

                        status:
                            status

                    })

                }
            )

            .then(async response => {

                const data =
                    await response.json();

                if (!response.ok) {

                    throw new Error(
                        data.message
                        ||
                        'Status update failed.'
                    );

                }

                return data;

            })

            .then(data => {

                this.selectedIds = [];

                this.fetchProducts();

                this.fetchStatistics();

                this.showMessage(
                    data.message
                );

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /* =========================================
           DUPLICATE PRODUCT
        ========================================= */

        duplicateProduct(id) {

            if (
                !confirm(
                    'Create a copy of this product?'
                )
            ) {

                return;

            }


            fetch(
                `/api/products/${id}/duplicate`,
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

                    }

                }
            )

            .then(async response => {

                const data =
                    await response.json();

                if (!response.ok) {

                    throw new Error(
                        data.message
                        ||
                        'Duplicate failed.'
                    );

                }

                return data;

            })

            .then(data => {

                this.fetchProducts();

                this.fetchStatistics();

                this.showMessage(
                    data.message
                );

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /* =========================================
           MESSAGE
        ========================================= */

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


        /* =========================================
           MODAL
        ========================================= */

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


        editProduct(product) {

            this.form = {

                id:
                    product.id,

                name:
                    product.name,

                price:
                    product.price,

                stock:
                    product.stock,

                status:
                    product.status

            };

            this.showModal = true;

        },


        /* =========================================
           SAVE PRODUCT
        ========================================= */

        saveProduct() {

            if (
                !this.form.name.trim()
            ) {

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


            const id =
                this.form.id;


            const url =
                id
                    ? `/api/products/${id}`
                    : '/api/products';


            const method =
                id
                    ? 'PUT'
                    : 'POST';


            fetch(
                url,
                {

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

                }
            )

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

                this.showModal = false;

                this.fetchProducts();

                this.fetchStatistics();

                this.showMessage(
                    id
                        ? 'Product updated successfully!'
                        : 'Product added successfully!'
                );

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /* =========================================
           DELETE
        ========================================= */

        deleteProduct(id) {

            if (
                !confirm(
                    'Are you sure you want to delete this product?'
                )
            ) {

                return;

            }


            fetch(
                `/api/products/${id}`,
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

            .then(response =>
                response.json()
            )

            .then(data => {

                this.fetchProducts();

                this.fetchStatistics();

                this.showMessage(
                    data.message
                    ||
                    'Product deleted successfully!'
                );

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

            });

        },


        /* =========================================
           IMPORT
        ========================================= */

        importFile(event) {

            const file =
                event.target.files[0];


            if (!file) {

                return;

            }


            const extension =
                file.name
                    .split('.')
                    .pop()
                    .toLowerCase();


            if (
                ![
                    'xlsx',
                    'xls',
                    'csv'
                ].includes(extension)
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
                'Importing Excel file...'
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

                    body:
                        formData

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

                this.importSummary = {

                    show: true,

                    created:
                        data.created || 0,

                    updated:
                        data.updated || 0,

                    imported:
                        data.imported || 0,

                    skipped:
                        data.skipped || 0,

                    total:
                        data.total || 0,

                    errors:
                        data.errors || [],

                    activities:
                        data.activities || []

                };


                this.fetchProducts();

                this.fetchStatistics();


                this.showMessage(
                    `Import completed: ${data.created || 0} created, ${data.updated || 0} updated, ${data.skipped || 0} skipped.`
                );


                event.target.value = '';

            })

            .catch(error => {

                this.showMessage(
                    error.message,
                    'error'
                );

                event.target.value = '';

            });

        },


        /* =========================================
           EXPORT ALL
        ========================================= */

        exportProducts() {

            window.location.href =
                '/api/products/export';

        },


        /* =========================================
           EXPORT FILTERED
        ========================================= */

        exportFiltered() {

            if (
                this.filteredProducts.length === 0
            ) {

                this.showMessage(
                    'No products available for export.',
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
                        this.stockFilter,

                    min_price:
                        this.minPrice,

                    max_price:
                        this.maxPrice,

                    start_date:
                        this.startDate,

                    end_date:
                        this.endDate

                });


            window.location.href =
                '/api/products/export-filtered?'
                +
                params.toString();

        },


        /* =========================================
           FORMAT DATE
        ========================================= */

        formatDate(date) {

            if (!date) {

                return '-';

            }


            return new Date(date)
                .toLocaleDateString();

        }

    };

}

</script>

</body>

</html>