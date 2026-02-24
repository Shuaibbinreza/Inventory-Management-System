@extends('inventory.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Products</h2>
        <a href="{{ route('inventory.create-product') }}" class="btn btn-primary">Add New Product</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Purchase Price</th>
                        <th>Sell Price</th>
                        <th>Opening Stock</th>
                        <th>Current Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->purchase_price, 2) }} TK</td>
                        <td>{{ number_format($product->sell_price, 2) }} TK</td>
                        <td>{{ $product->opening_stock }}</td>
                        <td><strong>{{ $product->getCurrentStock() }}</strong></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No products found. Add a product to get started.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
