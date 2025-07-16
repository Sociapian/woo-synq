@extends('layouts.app')

@section('title', 'Products - Indian Products Sync')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-box me-2"></i>
                Products
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>
                Add Product
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Product List
                </h5>
            </div>
            <div class="card-body">
                <div id="productsList">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" required>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="productPrice" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productImageUrl" class="form-label">Image URL (Optional)</label>
                        <input type="url" class="form-control" id="productImageUrl">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addProduct()">
                    <i class="fas fa-plus me-2"></i>
                    Add Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Edit Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="editProductId">
                    <div class="mb-3">
                        <label for="editProductName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editProductName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProductDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editProductDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editProductPrice" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="editProductPrice" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editProductImageUrl" class="form-label">Image URL (Optional)</label>
                        <input type="url" class="form-control" id="editProductImageUrl">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateProduct()">
                    <i class="fas fa-save me-2"></i>
                    Update Product
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let products = [];

// Load products
function loadProducts() {
    const token = localStorage.getItem('auth_token');
    
    fetch('/api/products', {
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        return response.text().then(text => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}\nResponse: ${text.substring(0, 200)}`);
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON response: ${text.substring(0, 200)}`);
            }
        });
    })
    .then(data => {
        products = data.products?.data || [];
        renderProducts();
    })
    .catch(error => {
        document.getElementById('productsList').innerHTML = `
            <div class="alert alert-danger">
                <h5>Error loading products</h5>
                <p>${error.message}</p>
                <button class="btn btn-primary" onclick="loadProducts()">Retry</button>
            </div>
        `;
    });
}

// Render products
function renderProducts() {
    if (products.length === 0) {
        document.getElementById('productsList').innerHTML = '<p class="text-muted text-center">No products found</p>';
        return;
    }
    
    const productsHtml = products.map(product => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">${product.description}</p>
                        <div class="d-flex align-items-center gap-3">
                            <span class="h5 text-primary mb-0">₹${product.price}</span>
                            <span class="badge ${getStatusBadgeClass(product.status)}">
                                ${product.status}
                            </span>
                            ${product.wc_product_id ? `<small class="text-muted">WC ID: ${product.wc_product_id}</small>` : ''}
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary btn-sm" onclick="editProduct(${product.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${product.status === 'failed' ? `
                                <button class="btn btn-outline-warning btn-sm" onclick="retrySync(${product.id})">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            ` : ''}
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteProduct(${product.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('productsList').innerHTML = productsHtml;
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'synced': return 'bg-success';
        case 'created': return 'bg-warning';
        case 'failed': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Add product
function addProduct() {
    const token = localStorage.getItem('auth_token');
    const name = document.getElementById('productName').value.trim();
    const description = document.getElementById('productDescription').value.trim();
    const price = document.getElementById('productPrice').value;
    const imageUrl = document.getElementById('productImageUrl').value.trim();
    if (!name) { alert('Product name is required'); return; }
    if (!description) { alert('Product description is required'); return; }
    if (!price || isNaN(price) || parseFloat(price) <= 0) { alert('Please enter a valid price (greater than 0)'); return; }
    const formData = { name: name, description: description, price: parseFloat(price), image_url: imageUrl || null };
    fetch('/api/products', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}${errorData.errors ? '\n' + JSON.stringify(errorData.errors, null, 2) : ''}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.product) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();
            document.getElementById('addProductForm').reset();
            loadProducts();
            alert('Product added successfully!');
        } else {
            alert('Error adding product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error adding product: ' + error.message);
    });
}

// Edit product
function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductName').value = product.name;
    document.getElementById('editProductDescription').value = product.description;
    document.getElementById('editProductPrice').value = product.price;
    document.getElementById('editProductImageUrl').value = product.image_url || '';
    const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    modal.show();
}

// Update product
function updateProduct() {
    const token = localStorage.getItem('auth_token');
    const productId = document.getElementById('editProductId').value;
    const formData = {
        name: document.getElementById('editProductName').value,
        description: document.getElementById('editProductDescription').value,
        price: document.getElementById('editProductPrice').value,
        image_url: document.getElementById('editProductImageUrl').value || null
    };
    fetch(`/api/products/${productId}`, {
        method: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}${errorData.errors ? '\n' + JSON.stringify(errorData.errors, null, 2) : ''}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.product) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
            modal.hide();
            loadProducts();
            alert('Product updated successfully!');
        } else {
            alert('Error updating product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error updating product: ' + error.message);
    });
}

// Delete product
function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    const token = localStorage.getItem('auth_token');
    fetch(`/api/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        loadProducts();
        alert('Product deleted successfully!');
    })
    .catch(error => {
        alert('Error deleting product: ' + error.message);
    });
}

// Retry sync
function retrySync(productId) {
    const token = localStorage.getItem('auth_token');
    fetch(`/api/products/${productId}/retry-sync`, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        loadProducts();
        alert('Sync retry completed!');
    })
    .catch(error => {
        alert('Error retrying sync: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', loadProducts);
</script>
@endsection 