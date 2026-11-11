<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'purchase_unit_price' => 'decimal:2',
        'yield_sticks_per_unit' => 'decimal:2',
        'cost_per_stick' => 'decimal:2',
        'previous_cost_price' => 'decimal:2',
        'price_change_amount' => 'decimal:2',
        'price_change_percent' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }
}
