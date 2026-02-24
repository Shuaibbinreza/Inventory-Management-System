@extends('inventory.layout')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Products</h5>
                    <h3>{{ $totalProducts }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Stock</h5>
                    <h3>{{ $totalStock }} units</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Today's Sales</h5>
                    <h3>{{ number_format($todaySales, 2) }} TK</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Today's Expenses</h5>
                    <h3>{{ number_format($todayExpenses, 2) }} TK</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('inventory.create-product') }}" class="btn btn-primary mb-2">Add New Product</a>
                    <a href="{{ route('inventory.create-sale') }}" class="btn btn-success mb-2">Record Sale</a>
                    <a href="{{ route('inventory.create-expense') }}" class="btn btn-warning mb-2">Add Expense</a>
                    <a href="{{ route('inventory.financial-report') }}" class="btn btn-info mb-2">View Financial Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">System Info</h5>
                </div>
                <div class="card-body">
                    <p><strong>Inventory Management System</strong></p>
                    <p>This system tracks:</p>
                    <ul>
                        <li>Products with purchase and sell prices</li>
                        <li>Opening stock and inventory transactions</li>
                        <li>Sales with discount and VAT</li>
                        <li>Accounting journal entries</li>
                        <li>Date-wise financial reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
