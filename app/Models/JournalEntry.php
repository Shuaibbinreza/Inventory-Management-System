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
        'entry_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    /**
     * Set entry_date from DD-MM-YYYY format
     */
    public function setEntryDateAttribute($value)
    {
        if ($value) {
            // Check if already in YYYY-MM-DD format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $this->attributes['entry_date'] = $value;
            } else {
                // Convert DD-MM-YYYY to YYYY-MM-DD
                $this->attributes['entry_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }
        } else {
            $this->attributes['entry_date'] = $value;
        }
    }

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
