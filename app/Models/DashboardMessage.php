<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardMessage extends Model
{
    protected $fillable = [
        'message',
        'type',
        'is_pinned',
        'account_id',
        'branch_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
