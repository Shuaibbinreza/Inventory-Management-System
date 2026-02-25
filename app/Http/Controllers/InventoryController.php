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
}
