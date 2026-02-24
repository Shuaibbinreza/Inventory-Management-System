<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
        .card { border: none; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .table { background-color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom border-secondary">
                    <h5>Inventory System</h5>
                </div>
                <nav class="mt-2">
                    <a href="{{ route('inventory.dashboard') }}" class="{{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('inventory.products') }}" class="{{ request()->routeIs('inventory.products') || request()->routeIs('inventory.create-product') ? 'active' : '' }}">Products</a>
                    <a href="{{ route('inventory.sales') }}" class="{{ request()->routeIs('inventory.sales') || request()->routeIs('inventory.create-sale') ? 'active' : '' }}">Sales</a>
                    <a href="{{ route('inventory.journal-entries') }}" class="{{ request()->routeIs('inventory.journal-entries') ? 'active' : '' }}">Journal Entries</a>
                    <a href="{{ route('inventory.financial-report') }}" class="{{ request()->routeIs('inventory.financial-report') || request()->routeIs('inventory.create-expense') ? 'active' : '' }}">Financial Report</a>
                    <a href="{{ route('inventory.init-demo') }}" class="text-warning">Load Demo Data</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
