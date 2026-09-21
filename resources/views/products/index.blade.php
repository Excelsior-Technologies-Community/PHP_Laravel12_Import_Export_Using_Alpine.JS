<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel 12 Alpine Product Manager - Smart Import & Export Studio</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SheetJS (xlsx.full.min.js) for in-browser client-side Excel/CSV parsing -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        .cell-error {
            background-color: #fee2e2 !important;
            border-color: #ef4444 !important;
            color: #991b1b !important;
        }
        .cell-valid {
            background-color: #f0fdf4;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased p-4 md:p-8">

<div class="max-w-7xl mx-auto" x-data="productApp()" x-init="init()" x-cloak>

    <!-- =====================================================
         1. TOP HEADER & QUICK ACTIONS
    ====================================================== -->
    <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📦</span>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Alpine Product Manager</h1>
                        <p class="text-xs text-slate-500 font-medium">Laravel 12 + Alpine.js • Column Mapping Wizard • In-Place Grid Fixer • Multi-Format Exporter</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Smart Import Wizard Button -->
                <button
                    @click="openMappingWizard()"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition hover:shadow">
                    <span>🎛️</span> Smart Import Wizard
                </button>

                <!-- Multi-Format Custom Export Button -->
                <button
                    @click="openExportModal()"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition hover:shadow">
                    <span>📊</span> Custom Export Builder
                </button>

                <!-- Add Single Product Button -->
                <button
                    @click="openModal()"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition hover:shadow">
                    <span>+</span> Add Product
                </button>
            </div>
        </div>
    </header>

    <!-- Global Alert / Notification Toast -->
    <div
        x-show="message"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-rose-50 text-rose-800 border-rose-300'"
        class="mb-6 px-4 py-3 rounded-xl border flex items-center justify-between text-sm font-medium shadow-sm">
        <div class="flex items-center gap-2">
            <span x-text="messageType === 'success' ? '✅' : '⚠️'"></span>
            <span x-text="message"></span>
        </div>
        <button @click="message = ''" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
    </div>

    <!-- =====================================================
         2. KPI STATS DASHBOARD
    ====================================================== -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Products</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="statistics.total"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Active Items</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1" x-text="statistics.active"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Inactive</p>
            <p class="text-2xl font-extrabold text-rose-500 mt-1" x-text="statistics.inactive"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total Stock</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1" x-text="statistics.total_stock"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Out / Low Stock</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">
                <span x-text="statistics.out_of_stock"></span> / <span x-text="statistics.low_stock"></span>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Inventory Value</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1" x-text="'$' + Number(statistics.inventory_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
        </div>
    </div>

    <!-- =====================================================
         3. PRODUCT FILTER TOOLBAR & BULK ACTIONS
    ====================================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Search -->
            <div class="relative">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Search Products</label>
                <input
                    type="search"
                    x-model="search"
                    placeholder="Search name, status..."
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Status</label>
                <select
                    x-model="statusFilter"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>
            </div>

            <!-- Stock Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Stock Level</label>
                <select
                    x-model="stockFilter"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All Stock Levels</option>
                    <option value="in_stock">In Stock (> 0)</option>
                    <option value="low_stock">Low Stock (1 - 10)</option>
                    <option value="out_of_stock">Out of Stock (0)</option>
                </select>
            </div>

            <!-- Min/Max Price -->
            <div class="flex gap-2">
                <div class="w-1/2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Min $</label>
                    <input type="number" x-model="minPrice" placeholder="0" min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-1/2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Max $</label>
                    <input type="number" x-model="maxPrice" placeholder="Max" min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-600" x-text="`Selected: ${selectedProducts.length} items`"></span>
                <template x-if="selectedProducts.length > 0">
                    <div class="flex items-center gap-2">
                        <button @click="bulkSetStatus('active')" class="bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-lg hover:bg-emerald-200">Set Active</button>
                        <button @click="bulkSetStatus('inactive')" class="bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded-lg hover:bg-slate-300">Set Inactive</button>
                        <button @click="bulkDelete()" class="bg-rose-100 text-rose-700 font-semibold px-2.5 py-1 rounded-lg hover:bg-rose-200">Delete Selected</button>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <button @click="resetFilters()" class="text-slate-500 hover:text-slate-700 font-medium">Reset Filters</button>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500 font-medium" x-text="`Showing ${filteredProducts.length} of ${products.length} products`"></span>
            </div>
        </div>
    </div>

    <!-- =====================================================
         4. PRODUCTS TABLE
    ====================================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-bold text-slate-600 tracking-wider">
                        <th class="p-4 w-12 text-center">
                            <input
                                type="checkbox"
                                :checked="selectedProducts.length > 0 && selectedProducts.length === filteredProducts.length"
                                @change="toggleSelectAll()"
                                class="rounded text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="p-4">Product Name</th>
                        <th class="p-4 text-right">Unit Price</th>
                        <th class="p-4 text-right">Stock</th>
                        <th class="p-4 text-right">Inventory Value</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4">Created Date</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="product in paginatedProducts" :key="product.id">
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 text-center">
                                <input
                                    type="checkbox"
                                    :value="product.id"
                                    x-model="selectedProducts"
                                    class="rounded text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block" x-text="product.name"></span>
                                <span class="text-xs text-slate-400" x-text="'ID: #' + product.id"></span>
                            </td>
                            <td class="p-4 text-right font-semibold text-slate-900" x-text="'$' + Number(product.price).toFixed(2)"></td>
                            <td class="p-4 text-right">
                                <span
                                    :class="product.stock === 0 ? 'text-rose-600 font-bold' : (product.stock <= 10 ? 'text-amber-600 font-semibold' : 'text-slate-700 font-medium')"
                                    x-text="product.stock + ' units'"></span>
                            </td>
                            <td class="p-4 text-right font-medium text-slate-600" x-text="'$' + (Number(product.price) * Number(product.stock)).toFixed(2)"></td>
                            <td class="p-4 text-center">
                                <span
                                    :class="product.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-bold uppercase"
                                    x-text="product.status"></span>
                            </td>
                            <td class="p-4 text-xs text-slate-500" x-text="formatDate(product.created_at)"></td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="editProduct(product)" class="p-1.5 hover:bg-slate-100 text-indigo-600 rounded-lg" title="Edit">✏️</button>
                                    <button @click="duplicateProduct(product.id)" class="p-1.5 hover:bg-slate-100 text-emerald-600 rounded-lg" title="Duplicate">📋</button>
                                    <button @click="deleteProduct(product.id)" class="p-1.5 hover:bg-slate-100 text-rose-600 rounded-lg" title="Delete">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredProducts.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <span class="text-3xl block mb-2">🔍</span>
                                No products found matching your filter criteria.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs font-medium text-slate-600">
            <div>
                Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to <span x-text="Math.min(currentPage * perPage, filteredProducts.length)"></span> of <span x-text="filteredProducts.length"></span> items
            </div>
            <div class="flex items-center gap-1">
                <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg disabled:opacity-40 hover:bg-slate-100">
                    Previous
                </button>
                <span class="px-3 py-1.5" x-text="`Page ${currentPage} of ${totalPages || 1}`"></span>
                <button
                    @click="currentPage++"
                    :disabled="currentPage >= totalPages"
                    class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg disabled:opacity-40 hover:bg-slate-100">
                    Next
                </button>
            </div>
        </div>
    </div>


    <!-- =====================================================
         MODAL 1: INTERACTIVE COLUMN MAPPING WIZARD (FEATURE 1)
    ====================================================== -->
    <div
        x-show="showMappingModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        x-transition>
        <div
            @click.away="showMappingModal = false"
            class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="bg-indigo-950 text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">🎛️ Interactive Column Mapping Wizard</h3>
                    <p class="text-xs text-indigo-300">Map non-standard Excel/CSV headers to system fields with live auto-detection</p>
                </div>
                <button @click="showMappingModal = false" class="text-indigo-300 hover:text-white font-bold text-lg">✕</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                
                <!-- File Upload / Drop Area -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-2">1. Upload Excel / CSV File</label>
                    <div
                        @click="$refs.fileInput.click()"
                        class="border-2 border-dashed border-indigo-200 hover:border-indigo-500 rounded-2xl p-6 text-center cursor-pointer bg-indigo-50/40 hover:bg-indigo-50 transition">
                        <input
                            type="file"
                            x-ref="fileInput"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileSelect($event)"
                            class="hidden">
                        <span class="text-3xl block mb-2">📁</span>
                        <p class="font-bold text-slate-800 text-sm" x-text="selectedFileName ? `Selected: ${selectedFileName}` : 'Click or Drag & Drop Excel (.xlsx, .xls) or CSV (.csv) file'"></p>
                        <p class="text-xs text-slate-500 mt-1">Supports irregular headers (e.g. Item Title, Rate, Qty, Availability)</p>
                    </div>
                </div>

                <!-- Presets Bar -->
                <div x-show="sheetHeaders.length > 0" class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-600 uppercase">Mapping Preset:</span>
                        <select
                            x-model="selectedPreset"
                            @change="applyPreset(selectedPreset)"
                            class="bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-medium">
                            <option value="auto">✨ Auto-Detected Matcher</option>
                            <option value="shopify">Shopify Catalog Format</option>
                            <option value="amazon">Amazon Inventory Format</option>
                            <option value="tally">Tally / ERP Standard</option>
                            <template x-for="p in customPresets" :key="p.name">
                                <option :value="p.name" x-text="`Custom: ${p.name}`"></option>
                            </template>
                        </select>
                    </div>
                    <button
                        @click="saveCurrentPreset()"
                        class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-800 font-semibold px-3 py-1 rounded-lg">
                        💾 Save As Preset
                    </button>
                </div>

                <!-- Target System Fields Mapping Grid -->
                <div x-show="sheetHeaders.length > 0" class="space-y-3">
                    <label class="block text-xs font-bold uppercase text-slate-600">2. Match Database Fields to File Columns</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Product Name -->
                        <div class="bg-white border border-slate-200 p-3 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-xs text-slate-800">Product Name <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] text-slate-400">Target: name</span>
                            </div>
                            <select
                                x-model="columnMapping.name"
                                @change="updateLiveMappedPreview()"
                                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Ignore / Skip --</option>
                                <template x-for="header in sheetHeaders" :key="header">
                                    <option :value="header" :selected="columnMapping.name === header" x-text="header"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Price -->
                        <div class="bg-white border border-slate-200 p-3 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-xs text-slate-800">Unit Price <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] text-slate-400">Target: price</span>
                            </div>
                            <select
                                x-model="columnMapping.price"
                                @change="updateLiveMappedPreview()"
                                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Ignore / Skip --</option>
                                <template x-for="header in sheetHeaders" :key="header">
                                    <option :value="header" :selected="columnMapping.price === header" x-text="header"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Stock -->
                        <div class="bg-white border border-slate-200 p-3 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-xs text-slate-800">Stock Quantity <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] text-slate-400">Target: stock</span>
                            </div>
                            <select
                                x-model="columnMapping.stock"
                                @change="updateLiveMappedPreview()"
                                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Ignore / Skip --</option>
                                <template x-for="header in sheetHeaders" :key="header">
                                    <option :value="header" :selected="columnMapping.stock === header" x-text="header"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="bg-white border border-slate-200 p-3 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-xs text-slate-800">Status (Active/Inactive)</span>
                                <span class="text-[11px] text-slate-400">Target: status</span>
                            </div>
                            <select
                                x-model="columnMapping.status"
                                @change="updateLiveMappedPreview()"
                                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Default: active --</option>
                                <template x-for="header in sheetHeaders" :key="header">
                                    <option :value="header" :selected="columnMapping.status === header" x-text="header"></option>
                                </template>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Live Mapped Sample Preview -->
                <div x-show="previewSampleRows.length > 0">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-2">3. Live Sample Data Preview (First 3 Rows)</label>
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold">
                                <tr>
                                    <th class="p-2.5">Mapped Name</th>
                                    <th class="p-2.5 text-right">Mapped Price</th>
                                    <th class="p-2.5 text-right">Mapped Stock</th>
                                    <th class="p-2.5 text-center">Mapped Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(row, idx) in previewSampleRows" :key="idx">
                                    <tr class="bg-white">
                                        <td class="p-2.5 font-semibold text-slate-900" x-text="row.name || '-'"></td>
                                        <td class="p-2.5 text-right" x-text="'$' + (row.price || 0)"></td>
                                        <td class="p-2.5 text-right" x-text="row.stock || 0"></td>
                                        <td class="p-2.5 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 uppercase" x-text="row.status || 'active'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 p-4 border-t border-slate-200 flex items-center justify-between">
                <button
                    @click="showMappingModal = false"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-800 px-4 py-2">
                    Cancel
                </button>
                <button
                    @click="proceedToSpreadsheetEditor()"
                    :disabled="!columnMapping.name || !columnMapping.price || !columnMapping.stock"
                    class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow transition">
                    👉 Next: Open Spreadsheet Grid & Fixer (<span x-text="rawParsedRows.length"></span> rows)
                </button>
            </div>
        </div>
    </div>


    <!-- =====================================================
         MODAL 2: CLIENT-SIDE SPREADSHEET DATA GRID & ERROR FIXER (FEATURE 2)
    ====================================================== -->
    <div
        x-show="showDataGridModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-950/70 backdrop-blur-sm"
        x-transition>
        <div
            @click.away="showDataGridModal = false"
            class="bg-white w-full max-w-6xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col h-[92vh]">
            
            <!-- Header with Validation Telemetry -->
            <div class="bg-slate-900 text-white p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">🔍 In-Browser Spreadsheet Editor & In-Place Error Fixer</h3>
                    <p class="text-xs text-slate-400">Edit cells in-place to fix invalid rows right in your browser before importing to database</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1 rounded-lg text-xs font-bold" x-text="`🟢 Valid: ${validGridCount}`"></span>
                    <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 px-3 py-1 rounded-lg text-xs font-bold" x-text="`🔴 Errors: ${invalidGridCount}`"></span>
                    <button @click="showDataGridModal = false" class="text-slate-400 hover:text-white font-bold ml-2 text-lg">✕</button>
                </div>
            </div>

            <!-- Toolbar Controls -->
            <div class="bg-slate-100 p-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <button
                        @click="gridFilter = 'all'"
                        :class="gridFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-slate-700'"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 transition">
                        Show All (<span x-text="gridRows.length"></span>)
                    </button>
                    <button
                        @click="gridFilter = 'errors'"
                        :class="gridFilter === 'errors' ? 'bg-rose-600 text-white font-bold' : 'bg-white text-slate-700'"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 transition">
                        ⚠️ Only Errors (<span x-text="invalidGridCount"></span>)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="autoFixAllSanitize()"
                        class="bg-white border border-amber-300 text-amber-800 hover:bg-amber-50 font-semibold px-3 py-1.5 rounded-lg shadow-sm">
                        ⚡ Auto-Clean & Fix Defaults
                    </button>
                    <button
                        @click="addBlankGridRow()"
                        class="bg-white border border-slate-300 text-slate-800 hover:bg-slate-50 font-semibold px-3 py-1.5 rounded-lg shadow-sm">
                        + Add Row
                    </button>
                </div>
            </div>

            <!-- Editable Table Body -->
            <div class="flex-1 overflow-auto p-4">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-200 text-slate-700 uppercase font-bold sticky top-0 z-10">
                        <tr>
                            <th class="p-2.5 w-12 text-center">#</th>
                            <th class="p-2.5">Product Name <span class="text-rose-500">*</span></th>
                            <th class="p-2.5 w-32 text-right">Price ($) <span class="text-rose-500">*</span></th>
                            <th class="p-2.5 w-28 text-right">Stock Qty <span class="text-rose-500">*</span></th>
                            <th class="p-2.5 w-32 text-center">Status</th>
                            <th class="p-2.5 w-44">Validation Status</th>
                            <th class="p-2.5 w-12 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(row, index) in filteredGridRows" :key="row._rowId">
                            <tr :class="row._errors.length > 0 ? 'bg-rose-50/50' : 'hover:bg-slate-50'">
                                <td class="p-2 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                
                                <!-- Editable Name -->
                                <td class="p-1.5">
                                    <input
                                        type="text"
                                        x-model="row.name"
                                        @input="validateRow(row)"
                                        :class="row._errorFields.includes('name') ? 'cell-error' : 'cell-valid'"
                                        placeholder="Product name"
                                        class="w-full border rounded px-2.5 py-1.5 text-xs font-semibold focus:outline-none">
                                </td>

                                <!-- Editable Price -->
                                <td class="p-1.5">
                                    <input
                                        type="text"
                                        x-model="row.price"
                                        @input="validateRow(row)"
                                        :class="row._errorFields.includes('price') ? 'cell-error' : 'cell-valid'"
                                        placeholder="0.00"
                                        class="w-full border rounded px-2.5 py-1.5 text-xs text-right font-semibold focus:outline-none">
                                </td>

                                <!-- Editable Stock -->
                                <td class="p-1.5">
                                    <input
                                        type="number"
                                        x-model="row.stock"
                                        @input="validateRow(row)"
                                        :class="row._errorFields.includes('stock') ? 'cell-error' : 'cell-valid'"
                                        placeholder="0"
                                        class="w-full border rounded px-2.5 py-1.5 text-xs text-right font-semibold focus:outline-none">
                                </td>

                                <!-- Editable Status -->
                                <td class="p-1.5">
                                    <select
                                        x-model="row.status"
                                        @change="validateRow(row)"
                                        class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </td>

                                <!-- Error Badges -->
                                <td class="p-2">
                                    <template x-if="row._errors.length === 0">
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                            <span>✓</span> Ready
                                        </span>
                                    </template>
                                    <template x-if="row._errors.length > 0">
                                        <div class="text-[11px] text-rose-600 font-semibold" x-text="row._errors.join(', ')"></div>
                                    </template>
                                </td>

                                <!-- Delete Row -->
                                <td class="p-2 text-center">
                                    <button @click="deleteGridRow(row._rowId)" class="text-slate-400 hover:text-rose-600 font-bold text-sm">✕</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Import Mode & Submit -->
            <div class="bg-slate-50 p-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-700">Import Mode:</span>
                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                        <input type="radio" value="add_new" x-model="gridImportMode" class="text-indigo-600">
                        <span>Add as New</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                        <input type="radio" value="update_existing" x-model="gridImportMode" class="text-indigo-600">
                        <span>Update Existing (by Name)</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="showDataGridModal = false"
                        class="text-xs font-semibold text-slate-600 hover:text-slate-800 px-4 py-2">
                        Back / Cancel
                    </button>
                    <button
                        @click="submitDirectImport()"
                        :disabled="isSubmittingImport || invalidGridCount > 0 || gridRows.length === 0"
                        class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-sm transition">
                        <span x-text="isSubmittingImport ? '⏳ Importing...' : `🚀 Import All Clean Rows (${validGridCount})`"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- =====================================================
         MODAL 3: MULTI-FORMAT CUSTOM EXPORT BUILDER (FEATURE 3)
    ====================================================== -->
    <div
        x-show="showExportModalState"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        x-transition>
        <div
            @click.away="showExportModalState = false"
            class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
            
            <div class="bg-emerald-950 text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">📊 Multi-Format Custom Export Builder</h3>
                    <p class="text-xs text-emerald-300">Choose output format, select custom columns & download in 1-click</p>
                </div>
                <button @click="showExportModalState = false" class="text-emerald-300 hover:text-white font-bold text-lg">✕</button>
            </div>

            <div class="p-6 space-y-6">
                
                <!-- 1. Format Selector Cards -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-2">1. Choose File Format</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        
                        <!-- XLSX -->
                        <div
                            @click="exportConfig.format = 'xlsx'"
                            :class="exportConfig.format === 'xlsx' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="border rounded-xl p-3 text-center cursor-pointer transition">
                            <span class="text-2xl block mb-1">📊</span>
                            <span class="font-bold text-xs block">Excel (.xlsx)</span>
                            <span class="text-[10px] text-slate-500">Styled spreadsheet</span>
                        </div>

                        <!-- CSV -->
                        <div
                            @click="exportConfig.format = 'csv'"
                            :class="exportConfig.format === 'csv' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="border rounded-xl p-3 text-center cursor-pointer transition">
                            <span class="text-2xl block mb-1">📄</span>
                            <span class="font-bold text-xs block">CSV (.csv)</span>
                            <span class="text-[10px] text-slate-500">Plain comma-delimited</span>
                        </div>

                        <!-- JSON -->
                        <div
                            @click="exportConfig.format = 'json'"
                            :class="exportConfig.format === 'json' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="border rounded-xl p-3 text-center cursor-pointer transition">
                            <span class="text-2xl block mb-1">📋</span>
                            <span class="font-bold text-xs block">JSON (.json)</span>
                            <span class="text-[10px] text-slate-500">REST API structured</span>
                        </div>

                        <!-- PDF HTML Report -->
                        <div
                            @click="exportConfig.format = 'pdf_html'"
                            :class="exportConfig.format === 'pdf_html' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:bg-slate-50'"
                            class="border rounded-xl p-3 text-center cursor-pointer transition">
                            <span class="text-2xl block mb-1">📑</span>
                            <span class="font-bold text-xs block">PDF Report</span>
                            <span class="text-[10px] text-slate-500">Printable with charts</span>
                        </div>

                    </div>
                </div>

                <!-- 2. Column Selector -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase text-slate-600">2. Select Columns to Include</label>
                        <div class="flex gap-2 text-xs">
                            <button @click="selectAllExportColumns(true)" class="text-indigo-600 hover:underline font-semibold">Select All</button>
                            <span class="text-slate-300">|</span>
                            <button @click="selectAllExportColumns(false)" class="text-slate-500 hover:underline">Deselect All</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <template x-for="col in availableExportColumns" :key="col.id">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-slate-300">
                                <input
                                    type="checkbox"
                                    :value="col.id"
                                    x-model="exportConfig.columns"
                                    class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- 3. Export Scope -->
                <div class="bg-emerald-50/60 border border-emerald-200 p-3.5 rounded-xl text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-emerald-950 block">Export Scope:</span>
                        <span class="text-emerald-700" x-text="`Will export ${filteredProducts.length} products matching current search & filters.`"></span>
                    </div>
                    <span class="text-2xl">📦</span>
                </div>

            </div>

            <div class="bg-slate-50 p-4 border-t border-slate-200 flex items-center justify-between">
                <button
                    @click="showExportModalState = false"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-800 px-4 py-2">
                    Cancel
                </button>
                <button
                    @click="triggerCustomExport()"
                    :disabled="exportConfig.columns.length === 0"
                    class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-sm transition">
                    📥 Download Export File
                </button>
            </div>
        </div>
    </div>


    <!-- =====================================================
         MODAL 4: ADD / EDIT SINGLE PRODUCT
    ====================================================== -->
    <div
        x-show="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        x-transition>
        <div
            @click.away="showModal = false"
            class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h3 class="font-bold text-base" x-text="form.id ? '✏️ Edit Product' : '+ Add New Product'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>

            <form @submit.prevent="saveProduct()" class="p-6 space-y-4 text-xs font-semibold">
                <div>
                    <label class="block text-slate-700 mb-1 uppercase">Product Name *</label>
                    <input type="text" x-model="form.name" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 mb-1 uppercase">Unit Price ($) *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-1 uppercase">Stock Qty *</label>
                        <input type="number" min="0" x-model="form.stock" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-700 mb-1 uppercase">Status *</label>
                    <select x-model="form.status" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl shadow">Save Product</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- =====================================================
     ALPINE.JS CONTROLLER SCRIPT
====================================================== -->
<script>
function productApp() {
    return {
        // Data State
        products: [],
        statistics: {},
        selectedProducts: [],
        message: '',
        messageType: 'success',

        // Filters & Pagination
        search: '',
        statusFilter: 'all',
        stockFilter: 'all',
        minPrice: '',
        maxPrice: '',
        currentPage: 1,
        perPage: 10,

        // Single Product Modal
        showModal: false,
        form: { id: null, name: '', price: '', stock: '', status: 'active' },

        // Feature 1: Interactive Column Mapping State
        showMappingModal: false,
        selectedFileName: '',
        rawParsedRows: [],
        sheetHeaders: [],
        selectedPreset: 'auto',
        columnMapping: { name: '', price: '', stock: '', status: '' },
        previewSampleRows: [],
        customPresets: JSON.parse(localStorage.getItem('product_mapping_presets') || '[]'),

        // Feature 2: In-Browser Spreadsheet Editor State
        showDataGridModal: false,
        gridRows: [],
        gridFilter: 'all', // 'all' | 'errors'
        gridImportMode: 'add_new',
        isSubmittingImport: false,

        // Feature 3: Multi-Format Custom Export State
        showExportModalState: false,
        exportConfig: {
            format: 'xlsx',
            columns: ['id', 'name', 'price', 'stock', 'status', 'inventory_value', 'created_at']
        },
        availableExportColumns: [
            { id: 'id', label: 'Product ID' },
            { id: 'name', label: 'Product Name' },
            { id: 'price', label: 'Unit Price' },
            { id: 'stock', label: 'Stock Qty' },
            { id: 'status', label: 'Status' },
            { id: 'inventory_value', label: 'Inventory Value' },
            { id: 'created_at', label: 'Created Date' },
            { id: 'updated_at', label: 'Updated Date' }
        ],

        // --------------------------------------------------
        // Lifecycle Init
        // --------------------------------------------------
        init() {
            this.fetchProducts();
            this.fetchStatistics();
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => { this.message = ''; }, 6000);
        },

        fetchProducts() {
            fetch('/api/products', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { this.products = data; });
        },

        fetchStatistics() {
            fetch('/api/products/statistics', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { this.statistics = data; });
        },

        // --------------------------------------------------
        // Filtered & Paginated Products Computations
        // --------------------------------------------------
        get filteredProducts() {
            return this.products.filter(p => {
                // Search
                if (this.search) {
                    const q = this.search.toLowerCase();
                    const matchName = (p.name || '').toLowerCase().includes(q);
                    const matchStatus = (p.status || '').toLowerCase().includes(q);
                    if (!matchName && !matchStatus) return false;
                }

                // Status
                if (this.statusFilter !== 'all' && p.status !== this.statusFilter) return false;

                // Stock
                if (this.stockFilter === 'in_stock' && p.stock <= 0) return false;
                if (this.stockFilter === 'low_stock' && (p.stock < 1 || p.stock > 10)) return false;
                if (this.stockFilter === 'out_of_stock' && p.stock !== 0) return false;

                // Price
                if (this.minPrice !== '' && Number(p.price) < Number(this.minPrice)) return false;
                if (this.maxPrice !== '' && Number(p.price) > Number(this.maxPrice)) return false;

                return true;
            });
        },

        get totalPages() {
            return Math.ceil(this.filteredProducts.length / this.perPage) || 1;
        },

        get paginatedProducts() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredProducts.slice(start, start + this.perPage);
        },

        resetFilters() {
            this.search = '';
            this.statusFilter = 'all';
            this.stockFilter = 'all';
            this.minPrice = '';
            this.maxPrice = '';
            this.currentPage = 1;
        },

        // --------------------------------------------------
        // Single CRUD Actions
        // --------------------------------------------------
        openModal() {
            this.form = { id: null, name: '', price: '', stock: '', status: 'active' };
            this.showModal = true;
        },

        editProduct(p) {
            this.form = { id: p.id, name: p.name, price: p.price, stock: p.stock, status: p.status };
            this.showModal = true;
        },

        saveProduct() {
            const isEdit = Boolean(this.form.id);
            const url = isEdit ? `/api/products/${this.form.id}` : '/api/products';
            const method = isEdit ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(this.form)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Validation error');
                return data;
            })
            .then(() => {
                this.showModal = false;
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage(isEdit ? 'Product updated successfully!' : 'Product added successfully!');
            })
            .catch(err => this.showMessage(err.message, 'error'));
        },

        deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product?')) return;
            fetch(`/api/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(() => {
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage('Product deleted.');
            });
        },

        duplicateProduct(id) {
            fetch(`/api/products/${id}/duplicate`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(() => {
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage('Product duplicated successfully!');
            });
        },

        // --------------------------------------------------
        // Bulk Actions
        // --------------------------------------------------
        toggleSelectAll() {
            if (this.selectedProducts.length === this.filteredProducts.length) {
                this.selectedProducts = [];
            } else {
                this.selectedProducts = this.filteredProducts.map(p => p.id);
            }
        },

        bulkSetStatus(status) {
            fetch('/api/products/bulk-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: this.selectedProducts, status: status })
            })
            .then(() => {
                this.selectedProducts = [];
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage('Selected products updated.');
            });
        },

        bulkDelete() {
            if (!confirm(`Delete ${this.selectedProducts.length} selected products?`)) return;
            fetch('/api/products/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: this.selectedProducts })
            })
            .then(() => {
                this.selectedProducts = [];
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage('Bulk deletion complete.');
            });
        },

        // ==================================================
        // FEATURE 1: INTERACTIVE COLUMN MAPPING WIZARD
        // ==================================================
        openMappingWizard() {
            this.selectedFileName = '';
            this.rawParsedRows = [];
            this.sheetHeaders = [];
            this.columnMapping = { name: '', price: '', stock: '', status: '' };
            this.previewSampleRows = [];
            this.showMappingModal = true;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.selectedFileName = file.name;
            const reader = new FileReader();

            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];

                    // Convert to raw JSON array of objects
                    const json = XLSX.utils.sheet_to_json(worksheet, { defval: '' });
                    if (!json || !json.length) {
                        alert('The selected file appears to be empty.');
                        return;
                    }

                    this.rawParsedRows = json;
                    this.sheetHeaders = Object.keys(json[0] || {});

                    // Auto-Header Matcher
                    this.autoDetectHeaders();
                    this.updateLiveMappedPreview();

                } catch (err) {
                    console.error('Failed to parse sheet:', err);
                    alert('Error reading Excel/CSV file: ' + err.message);
                }
            };

            reader.readAsArrayBuffer(file);
        },

        autoDetectHeaders() {
            const headers = this.sheetHeaders.map(h => ({ raw: h, clean: h.toLowerCase().trim().replace(/[^a-z0-9]/g, '') }));

            const findMatch = (keywords) => {
                for (const kw of keywords) {
                    const found = headers.find(h => h.clean === kw || h.clean.includes(kw));
                    if (found) return found.raw;
                }
                return '';
            };

            this.columnMapping.name = findMatch(['productname', 'itemname', 'name', 'itemtitle', 'title', 'product', 'item', 'description']) || (this.sheetHeaders[0] || '');
            this.columnMapping.price = findMatch(['unitprice', 'price', 'rate', 'cost', 'mrp', 'amount', 'val']) || (this.sheetHeaders[1] || '');
            this.columnMapping.stock = findMatch(['stockqty', 'stock', 'quantity', 'qty', 'inventory', 'count', 'units']) || (this.sheetHeaders[2] || '');
            this.columnMapping.status = findMatch(['status', 'active', 'state', 'availability', 'condition']) || '';
        },

        applyPreset(presetKey) {
            if (presetKey === 'auto') {
                this.autoDetectHeaders();
            } else if (presetKey === 'shopify') {
                this.columnMapping.name = this.sheetHeaders.find(h => /title/i.test(h)) || this.columnMapping.name;
                this.columnMapping.price = this.sheetHeaders.find(h => /variant price/i.test(h) || /price/i.test(h)) || this.columnMapping.price;
                this.columnMapping.stock = this.sheetHeaders.find(h => /inventory qty/i.test(h) || /stock/i.test(h)) || this.columnMapping.stock;
                this.columnMapping.status = this.sheetHeaders.find(h => /status/i.test(h)) || '';
            } else if (presetKey === 'amazon') {
                this.columnMapping.name = this.sheetHeaders.find(h => /item-name/i.test(h) || /title/i.test(h)) || this.columnMapping.name;
                this.columnMapping.price = this.sheetHeaders.find(h => /price/i.test(h)) || this.columnMapping.price;
                this.columnMapping.stock = this.sheetHeaders.find(h => /quantity/i.test(h) || /qty/i.test(h)) || this.columnMapping.stock;
                this.columnMapping.status = this.sheetHeaders.find(h => /status/i.test(h)) || '';
            } else {
                const custom = this.customPresets.find(p => p.name === presetKey);
                if (custom && custom.mapping) {
                    this.columnMapping = { ...custom.mapping };
                }
            }
            this.updateLiveMappedPreview();
        },

        saveCurrentPreset() {
            const name = prompt('Enter a name for this mapping preset:');
            if (!name) return;

            const existingIdx = this.customPresets.findIndex(p => p.name.toLowerCase() === name.toLowerCase());
            if (existingIdx >= 0) {
                this.customPresets[existingIdx].mapping = { ...this.columnMapping };
            } else {
                this.customPresets.push({ name: name, mapping: { ...this.columnMapping } });
            }
            localStorage.setItem('product_mapping_presets', JSON.stringify(this.customPresets));
            this.selectedPreset = name;
            alert(`Preset "${name}" saved successfully!`);
        },

        updateLiveMappedPreview() {
            const sample = this.rawParsedRows.slice(0, 3);
            this.previewSampleRows = sample.map(row => ({
                name: this.columnMapping.name ? row[this.columnMapping.name] : '',
                price: this.columnMapping.price ? this.cleanPriceValue(row[this.columnMapping.price]) : 0,
                stock: this.columnMapping.stock ? this.cleanStockValue(row[this.columnMapping.stock]) : 0,
                status: this.columnMapping.status ? this.cleanStatusValue(row[this.columnMapping.status]) : 'active'
            }));
        },

        cleanPriceValue(val) {
            if (val === null || val === undefined) return 0;
            const clean = String(val).replace(/[^0-9.-]/g, '');
            const num = parseFloat(clean);
            return isNaN(num) ? 0 : num;
        },

        cleanStockValue(val) {
            if (val === null || val === undefined) return 0;
            const clean = String(val).replace(/[^0-9-]/g, '');
            const num = parseInt(clean, 10);
            return isNaN(num) ? 0 : num;
        },

        cleanStatusValue(val) {
            if (!val) return 'active';
            const s = String(val).toLowerCase().trim();
            if (['active', '1', 'yes', 'true', 'in stock', 'available'].includes(s)) return 'active';
            if (['inactive', '0', 'no', 'false', 'out of stock', 'disabled'].includes(s)) return 'inactive';
            return 'active';
        },

        // ==================================================
        // FEATURE 2: SPREADSHEET DATA GRID & ERROR FIXER
        // ==================================================
        proceedToSpreadsheetEditor() {
            if (!this.rawParsedRows.length) return;

            this.gridRows = this.rawParsedRows.map((raw, idx) => {
                const rowObj = {
                    _rowId: 'row_' + idx + '_' + Date.now(),
                    name: this.columnMapping.name ? String(raw[this.columnMapping.name] || '').trim() : '',
                    price: this.columnMapping.price ? this.cleanPriceValue(raw[this.columnMapping.price]) : '',
                    stock: this.columnMapping.stock ? this.cleanStockValue(raw[this.columnMapping.stock]) : '',
                    status: this.columnMapping.status ? this.cleanStatusValue(raw[this.columnMapping.status]) : 'active',
                    _errors: [],
                    _errorFields: []
                };
                this.validateRow(rowObj);
                return rowObj;
            });

            this.showMappingModal = false;
            this.showDataGridModal = true;
        },

        validateRow(row) {
            const errors = [];
            const errorFields = [];

            // 1. Name Check
            if (!row.name || String(row.name).trim() === '') {
                errors.push('Name is required');
                errorFields.push('name');
            }

            // 2. Price Check
            const priceNum = parseFloat(row.price);
            if (row.price === '' || isNaN(priceNum) || priceNum < 0) {
                errors.push('Price must be ≥ 0');
                errorFields.push('price');
            }

            // 3. Stock Check
            const stockNum = parseInt(row.stock, 10);
            if (row.stock === '' || isNaN(stockNum) || stockNum < 0) {
                errors.push('Stock must be an integer ≥ 0');
                errorFields.push('stock');
            }

            // 4. Status Check
            if (!['active', 'inactive'].includes(String(row.status).toLowerCase())) {
                errors.push('Status must be active or inactive');
                errorFields.push('status');
            }

            row._errors = errors;
            row._errorFields = errorFields;
        },

        get filteredGridRows() {
            if (this.gridFilter === 'errors') {
                return this.gridRows.filter(r => r._errors.length > 0);
            }
            return this.gridRows;
        },

        get validGridCount() {
            return this.gridRows.filter(r => r._errors.length === 0).length;
        },

        get invalidGridCount() {
            return this.gridRows.filter(r => r._errors.length > 0).length;
        },

        addBlankGridRow() {
            const newRow = {
                _rowId: 'new_' + Date.now(),
                name: 'New Product',
                price: 10.00,
                stock: 5,
                status: 'active',
                _errors: [],
                _errorFields: []
            };
            this.validateRow(newRow);
            this.gridRows.unshift(newRow);
        },

        deleteGridRow(rowId) {
            this.gridRows = this.gridRows.filter(r => r._rowId !== rowId);
        },

        autoFixAllSanitize() {
            this.gridRows.forEach(row => {
                if (!row.name || row.name.trim() === '') {
                    row.name = 'Unnamed Product';
                }
                if (row.price === '' || isNaN(parseFloat(row.price)) || parseFloat(row.price) < 0) {
                    row.price = 0.00;
                }
                if (row.stock === '' || isNaN(parseInt(row.stock, 10)) || parseInt(row.stock, 10) < 0) {
                    row.stock = 0;
                }
                row.status = this.cleanStatusValue(row.status);
                this.validateRow(row);
            });
            this.showMessage('Auto-cleaned defaults applied to all rows!');
        },

        submitDirectImport() {
            if (this.invalidGridCount > 0) {
                alert(`Please fix the ${this.invalidGridCount} invalid rows before importing.`);
                return;
            }

            this.isSubmittingImport = true;
            const payloadRows = this.gridRows.map(r => ({
                name: r.name,
                price: parseFloat(r.price),
                stock: parseInt(r.stock, 10),
                status: r.status
            }));

            fetch('/api/products/import-direct', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    rows: payloadRows,
                    import_mode: this.gridImportMode
                })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Import failed.');
                return data;
            })
            .then(data => {
                this.showDataGridModal = false;
                this.fetchProducts();
                this.fetchStatistics();
                this.showMessage(`🎉 ${data.message}`);
            })
            .catch(err => {
                this.showMessage(err.message, 'error');
            })
            .finally(() => {
                this.isSubmittingImport = false;
            });
        },

        // ==================================================
        // FEATURE 3: MULTI-FORMAT CUSTOM EXPORT BUILDER
        // ==================================================
        openExportModal() {
            this.showExportModalState = true;
        },

        selectAllExportColumns(selectAll) {
            if (selectAll) {
                this.exportConfig.columns = this.availableExportColumns.map(c => c.id);
            } else {
                this.exportConfig.columns = [];
            }
        },

        triggerCustomExport() {
            if (!this.exportConfig.columns.length) {
                alert('Please select at least one column to export.');
                return;
            }

            const params = new URLSearchParams({
                format: this.exportConfig.format,
                search: this.search || '',
                status: this.statusFilter || 'all',
                stock_filter: this.stockFilter || 'all',
                min_price: this.minPrice || '',
                max_price: this.maxPrice || ''
            });

            // Append columns
            this.exportConfig.columns.forEach(col => {
                params.append('columns[]', col);
            });

            this.showExportModalState = false;

            if (this.exportConfig.format === 'pdf_html') {
                window.open('/api/products/export-custom?' + params.toString(), '_blank');
            } else {
                window.location.href = '/api/products/export-custom?' + params.toString();
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString();
        }
    };
}
</script>

</body>
</html>