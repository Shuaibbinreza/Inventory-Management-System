<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Export financial report to CSV
     */
    public function financialReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with('product')
            ->orderBy('sale_date', 'desc')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $filename = 'financial_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales, $expenses, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // Title
            fputcsv($file, ['Financial Report']);
            fputcsv($file, ['Period:', $startDate . ' to ' . $endDate]);
            fputcsv($file, []);

            // Sales Summary
            fputcsv($file, ['SALES SUMMARY']);
            fputcsv($file, ['Date', 'Product', 'Qty', 'Unit Price', 'Subtotal', 'Discount', 'VAT', 'Total', 'Paid', 'Due']);
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->sale_date,
                    $sale->product->name,
                    $sale->quantity,
                    number_format($sale->unit_price, 2),
                    number_format($sale->subtotal, 2),
                    number_format($sale->discount, 2),
                    number_format($sale->vat_amount, 2),
                    number_format($sale->total_amount, 2),
                    number_format($sale->paid_amount, 2),
                    number_format($sale->due_amount, 2),
                ]);
            }
            fputcsv($file, []);

            // Totals
            fputcsv($file, ['Total Sales:', '', '', '', '', '', '', number_format($sales->sum('total_amount'), 2)]);
            fputcsv($file, ['Total Discount:', '', '', '', '', '', '', number_format($sales->sum('discount'), 2)]);
            fputcsv($file, ['Total VAT:', '', '', '', '', '', '', number_format($sales->sum('vat_amount'), 2)]);

            $totalSales = $sales->sum('total_amount') - $sales->sum('vat_amount');
            fputcsv($file, ['Net Sales:', '', '', '', '', '', '', number_format($totalSales, 2)]);
            fputcsv($file, []);

            // Expenses Summary
            fputcsv($file, ['EXPENSES SUMMARY']);
            fputcsv($file, ['Date', 'Description', 'Category', 'Amount (TK)']);
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date,
                    $expense->description,
                    $expense->category,
                    number_format($expense->amount, 2),
                ]);
            }
            fputcsv($file, []);

            $totalExpenses = $expenses->sum('amount');
            fputcsv($file, ['Total Expenses:', '', '', number_format($totalExpenses, 2)]);
            fputcsv($file, []);

            // Profit/Loss
            $profitLoss = $totalSales - $totalExpenses;
            fputcsv($file, ['PROFIT/LOSS']);
            fputcsv($file, ['Net Profit/Loss:', number_format($profitLoss, 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export journal entries to CSV
     */
    public function journalEntries(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = JournalEntry::orderBy('entry_date', 'desc')->orderBy('id', 'asc');
        
        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }

        $journalEntries = $query->get();

        $filename = 'journal_entries' . ($startDate ? '_' . $startDate . '_to_' . $endDate : '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($journalEntries) {
            $file = fopen('php://output', 'w');

            // Title
            fputcsv($file, ['Journal Entries Report']);
            fputcsv($file, []);

            // Header
            fputcsv($file, ['Entry #', 'Date', 'Description', 'Account Code', 'Account Name', 'Type', 'Amount (TK)']);

            foreach ($journalEntries as $entry) {
                fputcsv($file, [
                    $entry->entry_number,
                    $entry->entry_date,
                    $entry->description,
                    $entry->account_code,
                    $entry->account_name,
                    strtoupper($entry->entry_type),
                    number_format($entry->amount, 2),
                ]);
            }

            fputcsv($file, []);
            
            // Summary
            $totalDebit = $journalEntries->where('entry_type', 'debit')->sum('amount');
            $totalCredit = $journalEntries->where('entry_type', 'credit')->sum('amount');
            
            fputcsv($file, ['Total Debit:', number_format($totalDebit, 2)]);
            fputcsv($file, ['Total Credit:', number_format($totalCredit, 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales to CSV
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Sale::with('product')->orderBy('sale_date', 'desc');
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }

        $sales = $query->get();

        $filename = 'sales' . ($startDate ? '_' . $startDate . '_to_' . $endDate : '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Sales Report']);
            fputcsv($file, []);

            fputcsv($file, ['ID', 'Date', 'Product', 'Qty', 'Unit Price', 'Subtotal', 'Discount', 'VAT Rate', 'VAT Amount', 'Total', 'Paid', 'Due']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->id,
                    $sale->sale_date,
                    $sale->product->name,
                    $sale->quantity,
                    number_format($sale->unit_price, 2),
                    number_format($sale->subtotal, 2),
                    number_format($sale->discount, 2),
                    number_format($sale->vat_rate, 2) . '%',
                    number_format($sale->vat_amount, 2),
                    number_format($sale->total_amount, 2),
                    number_format($sale->paid_amount, 2),
                    number_format($sale->due_amount, 2),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total', '', '', '', '', number_format($sales->sum('subtotal'), 2), number_format($sales->sum('discount'), 2), '', number_format($sales->sum('vat_amount'), 2), number_format($sales->sum('total_amount'), 2), number_format($sales->sum('paid_amount'), 2), number_format($sales->sum('due_amount'), 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
