@extends('inventory.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales</h2>
        <div>
            <a href="{{ route('inventory.export-sales') }}" class="btn btn-info">Export CSV</a>
            <a href="{{ route('inventory.create-sale') }}" class="btn btn-success">Record New Sale</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>VAT</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->id }}</td>
                        <td>{{ $sale->sale_date }}</td>
                        <td>{{ $sale->product->name }}</td>
                        <td>{{ $sale->quantity }}</td>
                        <td>{{ number_format($sale->unit_price, 2) }}</td>
                        <td>{{ number_format($sale->subtotal, 2) }}</td>
                        <td>{{ number_format($sale->discount, 2) }}</td>
                        <td>{{ number_format($sale->vat_amount, 2) }}</td>
                        <td><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
                        <td class="text-success">{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-danger">{{ number_format($sale->due_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center">No sales found. Record a sale to get started.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
