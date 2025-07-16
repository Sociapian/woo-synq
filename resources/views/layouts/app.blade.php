<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Products Sync')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .btn-primary {
            background-color: #007cba;
            border-color: #007cba;
        }
        .btn-primary:hover {
            background-color: #005a87;
            border-color: #005a87;
        }
        .status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-sync-alt me-2"></i>
                Laravel Products Sync
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">
                            <i class="fas fa-box me-1"></i>
                            Products
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <span id="userName">User</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check authentication status
        function checkAuth() {
            const token = localStorage.getItem('auth_token');
            const currentPath = window.location.pathname;
            
            // If we're on the welcome page and have a token, redirect to dashboard
            if (currentPath === '/' && token) {
                window.location.href = '/dashboard';
                return;
            }
            
            // If we're on protected pages and don't have a token, redirect to welcome
            if ((currentPath === '/dashboard' || currentPath === '/products') && !token) {
                window.location.href = '/';
                return;
            }
            
            // If we have a token, verify it and update user name
            if (token) {
                console.log('Checking auth with token:', token.substring(0, 20) + '...');
                fetch('/user', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        localStorage.removeItem('auth_token');
                        if (currentPath !== '/') {
                            window.location.href = '/';
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('User data received:', data);
                    document.getElementById('userName').textContent = data.user.name;
                })
                .catch(() => {
                    localStorage.removeItem('auth_token');
                    if (currentPath !== '/') {
                        window.location.href = '/';
                    }
                });
            }
        }

        function logout() {
            const token = localStorage.getItem('auth_token');
            if (token) {
                fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    }
                }).finally(() => {
                    localStorage.removeItem('auth_token');
                    window.location.href = '/';
                });
            }
        }

        // Check auth on page load
        document.addEventListener('DOMContentLoaded', checkAuth);
    </script>
    @yield('scripts')
</body>
</html> 