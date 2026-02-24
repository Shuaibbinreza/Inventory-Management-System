@extends('inventory.layout')

@section('content')
<div class="container">
    <h2 class="mb-4">Record New Sale</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory.store-sale') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->sell_price }}" data-stock="{{ $product->getCurrentStock() }}">
                                {{ $product->name }} - Stock: {{ $product->getCurrentStock() }} - Price: {{ number_format($product->sell_price, 2) }} TK
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="discount" class="form-label">Discount (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="0" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="vat_rate" class="form-label">VAT Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="vat_rate" name="vat_rate" value="5" min="0" max="100">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sale_date" class="form-label">Sale Date</label>
                        <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paid_amount" class="form-label">Paid Amount (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Amount</label>
                        <div id="total-amount" class="form-control bg-light">0.00 TK</div>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Record Sale</button>
                    <a href="{{ route('inventory.sales') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('product_id').addEventListener('change', calculateTotal);
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('discount').addEventListener('input', calculateTotal);
        document.getElementById('vat_rate').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const productSelect = document.getElementById('product_id');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption.dataset.price) || 0;
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;

            const subtotal = price * quantity;
            const afterDiscount = subtotal - discount;
            const vatAmount = afterDiscount * (vatRate / 100);
            const total = afterDiscount + vatAmount;

            document.getElementById('total-amount').textContent = total.toFixed(2) + ' TK (Subtotal: ' + subtotal.toFixed(2) + ' - Discount: ' + discount.toFixed(2) + ' + VAT: ' + vatAmount.toFixed(2) + ')';
        }
    </script>
</div>
@endsection
