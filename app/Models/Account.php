<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Authenticatable
{
    protected $fillable = [
        'username',
        'usertype',
        'password',
        'status',
        'branch_id',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the branch that the account belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
