<?php

namespace App\Models;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public static function current()
    {
        return static::where('is_current', true)->first();
    }
}
