@extends('inventory.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Journal Entries</h2>
        <a href="{{ route('inventory.export-journal-entries') }}" class="btn btn-success">Export CSV</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Entry #</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Amount (TK)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journalEntries as $entry)
                    <tr class="{{ $entry->entry_type == 'debit' ? 'text-danger' : 'text-success' }}">
                        <td>{{ $entry->entry_number }}</td>
                        <td>{{ $entry->entry_date }}</td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->account_code }}</td>
                        <td>{{ $entry->account_name }}</td>
                        <td><strong>{{ strtoupper($entry->entry_type) }}</strong></td>
                        <td>{{ number_format($entry->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No journal entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Accounting Chart Reference</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Asset Accounts (1xxx)</h6>
                    <ul>
                        <li>1000 - Cash</li>
                        <li>1200 - Inventory</li>
                        <li>1300 - Accounts Receivable</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Revenue Accounts (4xxx)</h6>
                    <ul>
                        <li>4000 - Sales Revenue</li>
                        <li>4100 - Sales Discount</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Expense Accounts (5xxx, 6xxx)</h6>
                    <ul>
                        <li>5000 - Cost of Goods Sold (COGS)</li>
                        <li>6000 - Expenses</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Liability/Equity Accounts (2xxx, 3xxx)</h6>
                    <ul>
                        <li>2200 - VAT Payable</li>
                        <li>3000 - Opening Stock Equity</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
