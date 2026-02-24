<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'expense_date',
        'category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * Get expense_date in DD-MM-YYYY format
     */
    public function getExpenseDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '';
    }

    /**
     * Create an expense with journal entries
     */
    public static function createWithJournalEntries(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $expense = self::create($data);

            // Create journal entries for expense
            $expense->createJournalEntries();

            return $expense;
        });
    }

    /**
     * Create journal entries for this expense
     */
    public function createJournalEntries(): void
    {
        // Debit: Expense account
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => $this->expense_date,
            'description' => 'Expense - ' . $this->description,
            'entry_type' => 'debit',
            'amount' => $this->amount,
            'account_code' => '6000',
            'account_name' => 'Expenses',
            'sale_id' => null,
            'product_id' => null,
        ]);

        // Credit: Cash/Bank account
        JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => $this->expense_date,
            'description' => 'Cash Payment - ' . $this->description,
            'entry_type' => 'credit',
            'amount' => $this->amount,
            'account_code' => '1000',
            'account_name' => 'Cash',
            'sale_id' => null,
            'product_id' => null,
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
