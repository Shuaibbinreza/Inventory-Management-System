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
        'transaction_date' => 'date:Y-m-d',
    ];

    /**
     * Set transaction_date from DD-MM-YYYY format
     */
    public function setTransactionDateAttribute($value)
    {
        if ($value) {
            // Check if already in YYYY-MM-DD format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $this->attributes['transaction_date'] = $value;
            } else {
                // Convert DD-MM-YYYY to YYYY-MM-DD
                $this->attributes['transaction_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }
        } else {
            $this->attributes['transaction_date'] = $value;
        }
    }

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
