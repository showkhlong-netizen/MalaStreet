<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'yield_sticks_per_unit' => 'decimal:2',
        'latest_cost_price' => 'decimal:2',
        'cost_per_stick' => 'decimal:2',
        'current_stock_sticks' => 'integer',
        'scan_names' => 'array',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class)->orderByDesc('recorded_at');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }
}
