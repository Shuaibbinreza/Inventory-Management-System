<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\InventoryTransaction;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display dashboard with overview
     */
    public function dashboard()
    {
        $products = Product::with('inventoryTransactions')->get();
        
        // Calculate totals
        $totalProducts = $products->count();
        $totalStock = $products->sum(function($product) {
            return $product->getCurrentStock();
        });
        
        $todaySales = Sale::whereDate('sale_date', today())->sum('total_amount');
        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        
        return view('inventory.dashboard', compact('totalProducts', 'totalStock', 'todaySales', 'todayExpenses'));
    }

    /**
     * Show product list
     */
    public function products()
    {
        $products = Product::with('inventoryTransactions')->get();
        return view('inventory.products', compact('products'));
    }

    /**
     * Show form to create product
     */
    public function createProduct()
    {
        return view('inventory.create-product');
    }

    /**
     * Store new product with opening stock
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'opening_stock' => 'required|integer|min:0',
        ]);

        // Use model method to create product with opening stock and journal entries
        Product::createWithOpeningStock($validated);

        return redirect()->route('inventory.products')->with('success', 'Product created successfully with opening stock!');
    }

    /**
     * Show sales form
     */
    public function createSale()
    {
        $products = Product::all();
        return view('inventory.create-sale', compact('products'));
    }

    /**
     * Process sale with journal entries
     * 
     * Sale Details:
     * - Sold: 10 units
     * - Sell Price: 200 TK/unit
     * - Subtotal: 10 * 200 = 2000 TK
     * - Discount: 50 TK
     * - VAT: 5% on (2000 - 50) = 5% * 1950 = 97.50 TK
     * - Total: 1950 + 97.50 = 2047.50 TK
     * - Paid: 1000 TK
     * - Due: 1047.50 TK
     */
    public function storeSale(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'paid_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Check stock availability
        $currentStock = $product->getCurrentStock();
        if ($currentStock < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock. Available: ' . $currentStock);
        }

        // Use model method to create sale with journal entries
        Sale::createWithJournalEntries($validated);

        return redirect()->route('inventory.sales')->with('success', 'Sale completed successfully with journal entries!');
    }

    /**
     * Show sales list
     */
    public function sales()
    {
        $sales = Sale::with('product')->orderBy('sale_date', 'desc')->get();
        
        // Format dates to DD-MM-YYYY
        $sales = $sales->map(function ($sale) {
            $sale->sale_date = \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y');
            return $sale;
        });
        
        return view('inventory.sales', compact('sales'));
    }

    /**
     * Show journal entries
     */
    public function journalEntries()
    {
        $journalEntries = JournalEntry::orderBy('entry_date', 'desc')
            ->orderBy('id', 'asc')
            ->get();
            
        // Format dates to DD-MM-YYYY
        $journalEntries = $journalEntries->map(function ($entry) {
            $entry->entry_date = \Carbon\Carbon::parse($entry->entry_date)->format('d-m-Y');
            return $entry;
        });
        
        return view('inventory.journal-entries', compact('journalEntries'));
    }

    /**
     * Show financial report with date filter
     */
    public function financialReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        // Get sales within date range
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with('product')
            ->orderBy('sale_date', 'desc')
            ->get();

        // Get expenses within date range
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $totalDiscount = $sales->sum('discount');
        $totalVat = $sales->sum('vat_amount');
        $totalPaid = $sales->sum('paid_amount');
        $totalDue = $sales->sum('due_amount');
        
        // Gross profit calculation
        $cogsTotal = 0;
        foreach ($sales as $sale) {
            $cogsTotal += $sale->quantity * $sale->product->purchase_price;
        }
        $grossProfit = $totalSales - $totalDiscount - $cogsTotal;
        $netProfit = $grossProfit - $totalExpenses;

        // Group by date for the report
        $salesByDate = $sales->groupBy('sale_date');
        $expensesByDate = $expenses->groupBy('expense_date');

        // Get all unique dates
        $allDates = collect($salesByDate->keys()->toArray())
            ->merge($expensesByDate->keys()->toArray())
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        // Format dates for display
        $sales = $sales->map(function ($sale) {
            $sale->sale_date = \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y');
            return $sale;
        });

        $expenses = $expenses->map(function ($expense) {
            $expense->expense_date = \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y');
            return $expense;
        });

        return view('inventory.financial-report', compact(
            'sales',
            'expenses',
            'totalSales',
            'totalExpenses',
            'totalDiscount',
            'totalVat',
            'totalPaid',
            'totalDue',
            'grossProfit',
            'netProfit',
            'cogsTotal',
            'salesByDate',
            'expensesByDate',
            'allDates',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show form to add expense
     */
    public function createExpense()
    {
        return view('inventory.create-expense');
    }

    /**
     * Store expense
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
        ]);

        // Use model method to create expense with journal entries
        Expense::createWithJournalEntries($validated);

        return redirect()->route('inventory.financial-report')->with('success', 'Expense added successfully!');
    }

    /**
     * Helper function to create journal entry
     */
    private function createJournalEntry($entryDate, $description, $entryType, $amount, $accountCode, $accountName, $saleId = null, $productId = null)
    {
        // Generate unique entry number
        $lastEntry = JournalEntry::orderBy('id', 'desc')->first();
        $entryNumber = 'JE-' . date('Ymd') . '-' . str_pad(($lastEntry ? $lastEntry->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        JournalEntry::create([
            'entry_number' => $entryNumber,
            'entry_date' => $entryDate,
            'description' => $description,
            'entry_type' => $entryType,
            'amount' => $amount,
            'account_code' => $accountCode,
            'account_name' => $accountName,
            'sale_id' => $saleId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Export financial report to CSV
     */
    public function exportFinancialReport(Request $request)
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
            fputcsv($file, ['Total Paid:', '', '', '', '', '', '', number_format($sales->sum('paid_amount'), 2)]);
            fputcsv($file, ['Total Due:', '', '', '', '', '', '', number_format($sales->sum('due_amount'), 2)]);
            fputcsv($file, []);

            // Expenses
            fputcsv($file, ['EXPENSES']);
            fputcsv($file, ['Date', 'Description', 'Category', 'Amount']);
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date,
                    $expense->description,
                    $expense->category,
                    number_format($expense->amount, 2),
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, ['Total Expenses:', '', '', number_format($expenses->sum('amount'), 2)]);
            fputcsv($file, []);

            // Summary
            $totalSales = $sales->sum('total_amount');
            $totalExpenses = $expenses->sum('amount');
            $totalDiscount = $sales->sum('discount');
            $totalVat = $sales->sum('vat_amount');
            
            $cogsTotal = 0;
            foreach ($sales as $sale) {
                $cogsTotal += $sale->quantity * $sale->product->purchase_price;
            }
            $grossProfit = $totalSales - $totalDiscount - $cogsTotal;
            $netProfit = $grossProfit - $totalExpenses;

            fputcsv($file, ['PROFIT SUMMARY']);
            fputcsv($file, ['Total Sales:', number_format($totalSales, 2)]);
            fputcsv($file, ['Less: Discount:', number_format($totalDiscount, 2)]);
            fputcsv($file, ['Less: COGS:', number_format($cogsTotal, 2)]);
            fputcsv($file, ['Gross Profit:', number_format($grossProfit, 2)]);
            fputcsv($file, ['Less: Expenses:', number_format($totalExpenses, 2)]);
            fputcsv($file, ['Net Profit:', number_format($netProfit, 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export journal entries to CSV
     */
    public function exportJournalEntries(Request $request)
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
    public function exportSales(Request $request)
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

    /**
     * Initialize demo data (for testing the scenario)
     */
    public function initDemoData()
    {
        DB::transaction(function () {
            // Create product with opening stock
            $product = Product::create([
                'name' => 'Sample Product',
                'purchase_price' => 100,
                'sell_price' => 200,
                'opening_stock' => 50,
            ]);

            // Create opening stock transaction
            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'opening',
                'quantity' => 50,
                'unit_price' => 100,
                'total_amount' => 5000,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Opening Stock',
            ]);

            // Create opening stock journal entries
            $this->createJournalEntry(
                now()->toDateString(),
                'Opening Stock - ' . $product->name,
                'debit',
                5000,
                '1200',
                'Inventory',
                null,
                $product->id
            );

            $this->createJournalEntry(
                now()->toDateString(),
                'Opening Stock - ' . $product->name,
                'credit',
                5000,
                '3000',
                'Opening Stock Equity',
                null,
                $product->id
            );

            // Record the sale: 10 units, discount 50, VAT 5%, paid 1000
            $sale = Sale::create([
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_price' => 200,
                'subtotal' => 2000,
                'discount' => 50,
                'vat_rate' => 5,
                'vat_amount' => 97.50,
                'total_amount' => 2047.50,
                'paid_amount' => 1000,
                'due_amount' => 1047.50,
                'sale_date' => now()->toDateString(),
            ]);

            // Create inventory transaction
            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'sale',
                'quantity' => 10,
                'unit_price' => 200,
                'total_amount' => 2000,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Sold to Customer',
            ]);

            // Create journal entries for the sale
            // COGS (debit)
            $cogs = 10 * 100; // 1000 TK
            $this->createJournalEntry(
                now()->toDateString(),
                'COGS - Sale #' . $sale->id,
                'debit',
                $cogs,
                '5000',
                'Cost of Goods Sold',
                $sale->id,
                $product->id
            );

            // Inventory reduction (credit)
            $this->createJournalEntry(
                now()->toDateString(),
                'Inventory Reduction - Sale #' . $sale->id,
                'credit',
                $cogs,
                '1200',
                'Inventory',
                $sale->id,
                $product->id
            );

            // Sales Revenue (credit) - 1950 (2000 - 50)
            $this->createJournalEntry(
                now()->toDateString(),
                'Sales Revenue - Sale #' . $sale->id,
                'credit',
                1950,
                '4000',
                'Sales Revenue',
                $sale->id,
                $product->id
            );

            // VAT Payable (credit) - 97.50
            $this->createJournalEntry(
                now()->toDateString(),
                'VAT Payable - Sale #' . $sale->id,
                'credit',
                97.50,
                '2200',
                'VAT Payable',
                $sale->id,
                $product->id
            );

            // Accounts Receivable (debit) - 1047.50
            $this->createJournalEntry(
                now()->toDateString(),
                'Accounts Receivable - Sale #' . $sale->id,
                'debit',
                1047.50,
                '1300',
                'Accounts Receivable',
                $sale->id,
                $product->id
            );

            // Cash Received (debit) - 1000
            $this->createJournalEntry(
                now()->toDateString(),
                'Cash Received - Sale #' . $sale->id,
                'debit',
                1000,
                '1000',
                'Cash',
                $sale->id,
                $product->id
            );

            // Sales Discount (debit) - 50
            $this->createJournalEntry(
                now()->toDateString(),
                'Sales Discount - Sale #' . $sale->id,
                'debit',
                50,
                '4100',
                'Sales Discount',
                $sale->id,
                $product->id
            );
        });

        return redirect()->route('inventory.dashboard')->with('success', 'Demo data initialized successfully!');
    }
}
