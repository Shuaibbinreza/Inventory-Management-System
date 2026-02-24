@extends('inventory.layout')

@section('content')
<div class="container">
    <h2 class="mb-4">Add Expense</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory.store-expense') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="general">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expense_date" class="form-label">Expense Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-warning">Add Expense</button>
                    <a href="{{ route('inventory.financial-report') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
