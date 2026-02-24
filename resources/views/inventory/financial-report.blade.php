@extends('inventory.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Financial Report</h2>
        <div>
            <a href="{{ route('inventory.export-financial-report', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">Export CSV</a>
            <a href="{{ route('inventory.create-expense') }}" class="btn btn-warning">Add Expense</a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.financial-report') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('inventory.financial-report') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Sales</h6>
                    <h4>{{ number_format($totalSales, 2) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Total Expenses</h6>
                    <h4>{{ number_format($totalExpenses, 2) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Gross Profit</h6>
                    <h4>{{ number_format($grossProfit, 2) }} TK</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6>Net Profit</h6>
                    <h4>{{ number_format($netProfit, 2) }} TK</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">Sales Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Total Sales Amount:</td>
                            <td class="text-end"><strong>{{ number_format($totalSales, 2) }} TK</strong></td>
                        </tr>
                        <tr>
                            <td>Total Discount:</td>
                            <td class="text-end">- {{ number_format($totalDiscount, 2) }} TK</td>
                        </tr>
                        <tr>
                            <td>Total VAT:</td>
                            <td class="text-end">+ {{ number_format($totalVat, 2) }} TK</td>
                        </tr>
                        <tr>
                            <td>COGS:</td>
                            <td class="text-end">- {{ number_format($cogsTotal, 2) }} TK</td>
                        </tr>
                        <tr class="border-top">
                            <td>Gross Profit:</td>
                            <td class="text-end"><strong>{{ number_format($grossProfit, 2) }} TK</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">Payment Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Total Paid:</td>
                            <td class="text-end text-success"><strong>{{ number_format($totalPaid, 2) }} TK</strong></td>
                        </tr>
                        <tr>
                            <td>Total Due:</td>
                            <td class="text-end text-danger"><strong>{{ number_format($totalDue, 2) }} TK</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td>Total Expenses:</td>
                            <td class="text-end text-danger"><strong>{{ number_format($totalExpenses, 2) }} TK</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td>Net Profit:</td>
                            <td class="text-end"><strong>{{ number_format($netProfit, 2) }} TK</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Date-wise Report -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Date-wise Financial Report</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sales (TK)</th>
                        <th>Expenses (TK)</th>
                        <th>Net (TK)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allDates as $date)
                    @php
                        $daySales = isset($salesByDate[$date]) ? $salesByDate[$date]->sum('total_amount') : 0;
                        $dayExpenses = isset($expensesByDate[$date]) ? $expensesByDate[$date]->sum('amount') : 0;
                        $dayNet = $daySales - $dayExpenses;
                    @endphp
                    <tr>
                        <td>{{ $date }}</td>
                        <td class="text-success">{{ number_format($daySales, 2) }}</td>
                        <td class="text-danger">{{ number_format($dayExpenses, 2) }}</td>
                        <td class="{{ $dayNet >= 0 ? 'text-success' : 'text-danger' }}"><strong>{{ number_format($dayNet, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No transactions found for the selected date range.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th>Total</th>
                        <th class="text-success">{{ number_format($totalSales, 2) }}</th>
                        <th class="text-danger">{{ number_format($totalExpenses, 2) }}</th>
                        <th class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
