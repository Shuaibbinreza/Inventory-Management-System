@extends('inventory.layout')

@section('content')
<div class="container">
    <h2 class="mb-4">Add New Product</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory.store-product') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="purchase_price" class="form-label">Purchase Price (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sell_price" class="form-label">Sell Price (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="sell_price" name="sell_price" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="opening_stock" class="form-label">Opening Stock</label>
                        <input type="number" class="form-control" id="opening_stock" name="opening_stock" value="0" required>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Product</button>
                    <a href="{{ route('inventory.products') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
