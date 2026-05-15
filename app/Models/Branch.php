<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name', 'location', 'code'];

    /**
     * Get all accounts associated with this branch.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
