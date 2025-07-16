@extends('layouts.app')

@section('title', 'Dashboard - Indian Products Sync')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </h1>
            <a href="/products" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Add Product
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Products</h6>
                        <h3 class="mb-0" id="totalProducts">-</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Synced</h6>
                        <h3 class="mb-0" id="syncedProducts">-</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Created</h6>
                        <h3 class="mb-0" id="createdProducts">-</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Failed</h6>
                        <h3 class="mb-0" id="failedProducts">-</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Recent Products
                </h5>
            </div>
            <div class="card-body">
                <div id="recentProducts">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/products" class="btn btn-outline-primary">
                        <i class="fas fa-box me-2"></i>
                        Manage Products
                    </a>
                    <button class="btn btn-outline-info" onclick="testWooCommerceConnection()">
                        <i class="fas fa-plug me-2"></i>
                        Test WooCommerce Connection
                    </button>
                    <button class="btn btn-outline-warning" onclick="retryFailedSyncs()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Retry Failed Syncs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Load dashboard data
function loadDashboardData() {
    const token = localStorage.getItem('auth_token');
    fetch('/api/products', {
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const products = data.products.data || [];
        document.getElementById('totalProducts').textContent = products.length;
        document.getElementById('syncedProducts').textContent = products.filter(p => p.status === 'synced').length;
        document.getElementById('createdProducts').textContent = products.filter(p => p.status === 'created').length;
        document.getElementById('failedProducts').textContent = products.filter(p => p.status === 'failed').length;
        const recentProductsHtml = products.slice(0, 5).map(product => `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <h6 class="mb-1">${product.name}</h6>
                    <small class="text-muted">₹${product.price}</small>
                </div>
                <div>
                    <span class="badge ${getStatusBadgeClass(product.status)}">
                        ${product.status}
                    </span>
                </div>
            </div>
        `).join('');
        document.getElementById('recentProducts').innerHTML = recentProductsHtml || '<p class="text-muted">No products found</p>';
    })
    .catch(error => {
        // Optionally show an error message
    });
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'synced': return 'bg-success';
        case 'created': return 'bg-warning';
        case 'failed': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function testWooCommerceConnection() {
    const token = localStorage.getItem('auth_token');
    fetch('/woocommerce/test', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ WooCommerce connection successful!');
        } else {
            alert('❌ WooCommerce connection failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error testing WooCommerce connection: ' + error.message);
    });
}

function retryFailedSyncs() {
    const token = localStorage.getItem('auth_token');
    fetch('/api/products', {
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const failedProducts = data.products.data.filter(p => p.status === 'failed');
        if (failedProducts.length === 0) {
            alert('No failed products to retry.');
            return;
        }
        if (confirm(`Retry sync for ${failedProducts.length} failed products?`)) {
            alert('Bulk retry functionality would be implemented here.');
        }
    })
    .catch(error => {
        alert('Error loading products: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endsection 