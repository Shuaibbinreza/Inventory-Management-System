<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\DateFormatTrait;

class JournalEntry extends Model
{
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'entry_type',
        'amount',
        'account_code',
        'account_name',
        'sale_id',
        'product_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get entry_date in DD-MM-YYYY format
     */
    public function getEntryDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '';
    }
}
