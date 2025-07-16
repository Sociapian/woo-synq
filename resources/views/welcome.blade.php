@extends('layouts.app')

@section('title', 'Welcome - Laravel Products Sync')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary">
                <i class="fas fa-sync-alt me-3"></i>
                Laravel Woocomerce Synq
            </h1>
            <p class="lead">Manage your Laravel products and sync them to WooCommerce seamlessly</p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="loginEmail" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="loginPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </button>
                        </form>
                        <div id="loginMessage" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Register
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="registerName" class="form-label">Name</label>
                                <input type="text" class="form-control" id="registerName" required>
                            </div>
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="registerEmail" required>
                            </div>
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="registerPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="registerPasswordConfirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="registerPasswordConfirmation" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-user-plus me-2"></i>
                                Register
                            </button>
                        </form>
                        <div id="registerMessage" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const messageDiv = document.getElementById('loginMessage');
    
    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            localStorage.setItem('auth_token', data.token);
            messageDiv.innerHTML = '<div class="alert alert-success">Login successful! Redirecting...</div>';
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger">Login failed: ' + (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        messageDiv.innerHTML = '<div class="alert alert-danger">Login failed: ' + error.message + '</div>';
    });
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const passwordConfirmation = document.getElementById('registerPasswordConfirmation').value;
    const messageDiv = document.getElementById('registerMessage');
    
    if (password !== passwordConfirmation) {
        messageDiv.innerHTML = '<div class="alert alert-danger">Passwords do not match</div>';
        return;
    }
    
    fetch('/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            name: name,
            email: email,
            password: password,
            password_confirmation: passwordConfirmation
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            localStorage.setItem('auth_token', data.token);
            messageDiv.innerHTML = '<div class="alert alert-success">Registration successful! Redirecting...</div>';
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger">Registration failed: ' + (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        messageDiv.innerHTML = '<div class="alert alert-danger">Registration failed: ' + error.message + '</div>';
    });
});

// The layout's checkAuth function will handle authentication checks
</script>
@endsection
