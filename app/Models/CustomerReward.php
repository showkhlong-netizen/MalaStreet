<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerReward extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'points' => 'integer',
        'total_stamps' => 'integer',
        'free_skewers_redeemed' => 'integer',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class, 'customer_phone', 'customer_phone');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CustomerReview::class, 'customer_phone', 'customer_phone');
    }
}

