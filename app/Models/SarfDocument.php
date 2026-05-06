<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SarfDocument extends Model
{
    //
    protected $fillable = [
        'type',
        'file_path',
        'original_filename',
        'activity_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class);
    }
}
