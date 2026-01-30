<!DOCTYPE html>
<html>
<head>
    <title>Alpine Product Manager</title>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 p-10">

<div class="max-w-6xl mx-auto" x-data="productApp()" x-init="fetchProducts()">

    <h1 class="text-3xl font-bold mb-6">Alpine Product Manager</h1>

    <!-- 🔔 MESSAGE -->
    <div x-show="message"
         x-transition
         :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
         class="mb-4 px-4 py-2 rounded shadow"
         x-text="message">
    </div>

    <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
        + Add Product
    </button>

    <!-- 📋 TABLE -->
    <table class="w-full bg-white shadow rounded">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2">Name</th>
                <th class="p-2">Price</th>
                <th class="p-2">Stock</th>
                <th class="p-2">Status</th>
                <th class="p-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="product in products" :key="product.id">
                <tr class="border-t">
                    <td class="p-2" x-text="product.name"></td>
                    <td class="p-2" x-text="product.price"></td>
                    <td class="p-2" x-text="product.stock"></td>
                    <td class="p-2" x-text="product.status"></td>
                    <td class="p-2 flex gap-2">
                        <button @click="editProduct(product)" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</button>
                        <button @click="deleteProduct(product.id)" class="bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- 📥 IMPORT / 📤 EXPORT -->
    <div class="mt-6 bg-white p-4 rounded shadow">
        <input type="file" @change="importFile($event)">
        <button @click="exportProducts()" class="bg-green-600 text-white px-3 py-1 rounded ml-2">
            Export
        </button>
    </div>

    <!-- 🪟 MODAL -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">
        <div class="bg-white p-6 rounded w-96">
            <h2 class="text-xl font-bold mb-4" x-text="form.id ? 'Edit Product' : 'Add Product'"></h2>

            <input x-model="form.name" placeholder="Name" class="border p-2 w-full mb-2">
            <input x-model="form.price" type="number" placeholder="Price" class="border p-2 w-full mb-2">
            <input x-model="form.stock" type="number" placeholder="Stock" class="border p-2 w-full mb-2">

            <select x-model="form.status" class="border p-2 w-full mb-2">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="flex justify-end gap-2">
                <button @click="showModal=false" class="border px-3 py-1 rounded">Cancel</button>
                <button @click="saveProduct()" class="bg-blue-600 text-white px-3 py-1 rounded">Save</button>
            </div>
        </div>
    </div>

</div>

<script>
function productApp() {
    return {
        products: [],
        showModal: false,

        message: '',
        messageType: 'success',

        form: { id: null, name: '', price: '', stock: '', status: 'active' },

        showMessage(text, type = 'success') {
            this.message = text;
            this.messageType = type;
            setTimeout(() => this.message = '', 3000);
        },

        fetchProducts() {
            fetch('/api/products')
                .then(res => res.json())
                .then(data => this.products = data);
        },

        openModal() {
            this.form = { id: null, name: '', price: '', stock: '', status: 'active' };
            this.showModal = true;
        },

        editProduct(product) {
            this.form = {...product};
            this.showModal = true;
        },

        saveProduct() {
            let url = '/api/products' + (this.form.id ? '/' + this.form.id : '');
            let method = this.form.id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(() => {
                this.fetchProducts();
                this.showModal = false;
                this.showMessage(this.form.id ? 'Product updated successfully!' : 'Product added successfully!');
            })
            .catch(() => this.showMessage('Something went wrong!', 'error'));
        },

        deleteProduct(id) {
            if (!confirm('Delete this product?')) return;

            fetch('/api/products/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(() => {
                this.fetchProducts();
                this.showMessage('Product deleted successfully!');
            });
        },

        importFile(e) {
            let formData = new FormData();
            formData.append('file', e.target.files[0]);

            fetch('/api/products/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(() => {
                this.fetchProducts();
                this.showMessage('Products imported successfully!');
            });
        },

        exportProducts() {
            this.showMessage('Export started! File downloading...');
            setTimeout(() => {
                window.location.href = '/api/products/export';
            }, 800);
        }
    }
}
</script>

</body>
</html>
