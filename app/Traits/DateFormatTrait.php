<?php

namespace App\Traits;

/**
 * Trait for formatting dates in DD-MM-YYYY format
 */
trait DateFormatTrait
{
    /**
     * Format date to DD-MM-YYYY
     */
    public function getFormattedDate($date)
    {
        if (!$date) return '';
        
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('d-m-Y');
        }
        
        return \Carbon\Carbon::parse($date)->format('d-m-Y');
    }

    /**
     * Get date in DD-MM-YYYY format for display
     */
    public function getDateAttribute($value)
    {
        if (!$value) return '';
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Get created_at in DD-MM-YYYY format
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('d-m-Y H:i:s') : '';
    }

    /**
     * Get updated_at in DD-MM-YYYY format
     */
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? \Carbon\Carbon::parse($this->updated_at)->format('d-m-Y H:i:s') : '';
    }

    /**
     * Get formatted date attribute accessor
     * Use this in model: $model->formatted_date
     */
    public function getFormattedDateAttribute()
    {
        return $this->getFormattedDate($this->transaction_date ?? $this->entry_date ?? $this->sale_date ?? $this->expense_date ?? null);
    }
}
