<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'purchase_price',
        'sell_price',
        'opening_stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'opening_stock' => 'integer',
    ];

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getCurrentStock(): int
    {
        $purchases = $this->inventoryTransactions()->where('type', 'purchase')->sum('quantity');
        $sales = $this->inventoryTransactions()->where('type', 'sale')->sum('quantity');
        $openings = $this->inventoryTransactions()->where('type', 'opening')->sum('quantity');
        
        return $this->opening_stock + $purchases - $sales;
    }

    /**
     * Create product with opening stock and journal entries
     */
    public static function createWithOpeningStock(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $product = self::create($data);

            if ($product->opening_stock > 0) {
                // Create opening stock inventory transaction
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => 'opening',
                    'quantity' => $product->opening_stock,
                    'unit_price' => $product->purchase_price,
                    'total_amount' => $product->opening_stock * $product->purchase_price,
                    'transaction_date' => now()->toDateString(),
                    'notes' => 'Opening Stock',
                ]);

                // Create journal entries for opening stock
                $product->createOpeningStockJournalEntries();
            }

            return $product;
        });
    }

    /**
     * Create journal entries for opening stock
     */
    public function createOpeningStockJournalEntries(): void
    {
        $amount = $this->opening_stock * $this->purchase_price;

        // Debit: Inventory
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'description' => 'Opening Stock - ' . $this->name,
            'entry_type' => 'debit',
            'amount' => $amount,
            'account_code' => '1200',
            'account_name' => 'Inventory',
            'sale_id' => null,
            'product_id' => $this->id,
        ]);

        // Credit: Opening Stock Equity
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'description' => 'Opening Stock - ' . $this->name,
            'entry_type' => 'credit',
            'amount' => $amount,
            'account_code' => '3000',
            'account_name' => 'Opening Stock Equity',
            'sale_id' => null,
            'product_id' => $this->id,
        ]);
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
