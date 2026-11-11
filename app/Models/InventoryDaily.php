<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDaily extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'record_date' => 'date',
        'opening_sticks' => 'integer',
        'added_sticks' => 'integer',
        'sold_sticks' => 'integer',
        'waste_sticks' => 'integer',
        'expected_closing_sticks' => 'integer',
        'actual_closing_sticks' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
