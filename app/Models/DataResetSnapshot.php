<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataResetSnapshot extends Model
{
    protected $fillable = [
        'batch_number',
        'scope_key',
        'scope_label',
        'branch_id',
        'date_filter',
        'records_count',
        'total_amount',
        'summary_notes',
        'payload',
        'performed_by',
        'performer_name',
        'status',
        'restored_at',
        'restored_by',
        'restorer_name',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'records_count' => 'integer',
        'total_amount' => 'decimal:2',
        'restored_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
