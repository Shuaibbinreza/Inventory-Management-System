<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\DateFormatTrait;

class InventoryTransaction extends Model
{
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'unit_price',
        'total_amount',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get transaction_date in DD-MM-YYYY format
     */
    public function getTransactionDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '';
    }
}
