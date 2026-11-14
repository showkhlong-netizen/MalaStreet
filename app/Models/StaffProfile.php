<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'employee_code',
        'first_name',
        'last_name',
        'phone',
        'is_active',
        'has_custom_permissions',
        'permissions_updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_custom_permissions' => 'boolean',
        'permissions_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
