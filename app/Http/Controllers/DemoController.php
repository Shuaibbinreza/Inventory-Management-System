<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
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

    /**
     * Create a journal entry
     */
    private function createJournalEntry($entryDate, $description, $entryType, $amount, $accountCode, $accountName, $saleId = null, $productId = null)
    {
        $latestEntry = JournalEntry::orderBy('id', 'desc')->first();
        $entryNumber = 'JE-' . str_pad(($latestEntry ? $latestEntry->id + 1 : 1), 5, '0', STR_PAD_LEFT);

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
}
