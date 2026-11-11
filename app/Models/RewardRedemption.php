<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'points_spent' => 'integer',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}

