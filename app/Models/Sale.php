<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Traits\DateFormatTrait;

class Sale extends Model
{
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'sale_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get sale_date in DD-MM-YYYY format
     */
    public function getSaleDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '';
    }

    /**
     * Create a sale with inventory and journal entries
     */
    public static function createWithJournalEntries(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            
            // Calculate amounts
            $quantity = $data['quantity'];
            $discount = $data['discount'] ?? 0;
            $vatRate = $data['vat_rate'] ?? 0;
            $paidAmount = $data['paid_amount'];
            
            $subtotal = $quantity * $product->sell_price;
            $afterDiscount = $subtotal - $discount;
            $vatAmount = $afterDiscount * ($vatRate / 100);
            $totalAmount = $afterDiscount + $vatAmount;
            $dueAmount = $totalAmount - $paidAmount;

            // Create sale record
            $sale = self::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->sell_price,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'sale_date' => $data['sale_date'],
            ]);

            // Create inventory transaction (stock out)
            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'sale',
                'quantity' => $quantity,
                'unit_price' => $product->sell_price,
                'total_amount' => $subtotal,
                'transaction_date' => $data['sale_date'],
                'notes' => 'Sale to Customer',
            ]);

            // Create journal entries
            $sale->createJournalEntries($product, $quantity, $discount, $vatAmount, $paidAmount);

            return $sale;
        });
    }

    /**
     * Create all journal entries for this sale
     */
    public function createJournalEntries(Product $product, int $quantity, float $discount, float $vatAmount, float $paidAmount): void
    {
        $cogs = $quantity * $product->purchase_price;
        $afterDiscount = $this->subtotal - $discount;

        // COGS (debit)
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => $this->sale_date,
            'description' => 'COGS - Sale #' . $this->id . ' - ' . $product->name,
            'entry_type' => 'debit',
            'amount' => $cogs,
            'account_code' => '5000',
            'account_name' => 'Cost of Goods Sold',
            'sale_id' => $this->id,
            'product_id' => $product->id,
        ]);

        // Inventory reduction (credit)
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => $this->sale_date,
            'description' => 'Inventory Reduction - Sale #' . $this->id . ' - ' . $product->name,
            'entry_type' => 'credit',
            'amount' => $cogs,
            'account_code' => '1200',
            'account_name' => 'Inventory',
            'sale_id' => $this->id,
            'product_id' => $product->id,
        ]);

        // Sales Revenue (credit)
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => $this->sale_date,
            'description' => 'Sales Revenue - Sale #' . $this->id . ' - ' . $product->name,
            'entry_type' => 'credit',
            'amount' => $afterDiscount,
            'account_code' => '4000',
            'account_name' => 'Sales Revenue',
            'sale_id' => $this->id,
            'product_id' => $product->id,
        ]);

        // VAT Payable (credit)
        if ($vatAmount > 0) {
            JournalEntry::create([
                'entry_number' => $this->generateEntryNumber(),
                'entry_date' => $this->sale_date,
                'description' => 'VAT Payable - Sale #' . $this->id,
                'entry_type' => 'credit',
                'amount' => $vatAmount,
                'account_code' => '2200',
                'account_name' => 'VAT Payable',
                'sale_id' => $this->id,
                'product_id' => $product->id,
            ]);
        }

        // Accounts Receivable (debit) - if due amount
        if ($this->due_amount > 0) {
            JournalEntry::create([
                'entry_number' => $this->generateEntryNumber(),
                'entry_date' => $this->sale_date,
                'description' => 'Accounts Receivable - Sale #' . $this->id,
                'entry_type' => 'debit',
                'amount' => $this->due_amount,
                'account_code' => '1300',
                'account_name' => 'Accounts Receivable',
                'sale_id' => $this->id,
                'product_id' => $product->id,
            ]);
        }

        // Cash Received (debit)
        if ($paidAmount > 0) {
            JournalEntry::create([
                'entry_number' => $this->generateEntryNumber(),
                'entry_date' => $this->sale_date,
                'description' => 'Cash Received - Sale #' . $this->id,
                'entry_type' => 'debit',
                'amount' => $paidAmount,
                'account_code' => '1000',
                'account_name' => 'Cash',
                'sale_id' => $this->id,
                'product_id' => $product->id,
            ]);
        }

        // Sales Discount (debit)
        if ($discount > 0) {
            JournalEntry::create([
                'entry_number' => $this->generateEntryNumber(),
                'entry_date' => $this->sale_date,
                'description' => 'Sales Discount - Sale #' . $this->id,
                'entry_type' => 'debit',
                'amount' => $discount,
                'account_code' => '4100',
                'account_name' => 'Sales Discount',
                'sale_id' => $this->id,
                'product_id' => $product->id,
            ]);
        }
    }

    /**
     * Generate unique journal entry number
     */
    private function generateEntryNumber(): string
    {
        $lastEntry = JournalEntry::orderBy('id', 'desc')->first();
        return 'JE-' . date('Ymd') . '-' . str_pad(($lastEntry ? $lastEntry->id + 1 : 1), 4, '0', STR_PAD_LEFT);
    }
}
