<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    //
    protected $fillable = [
        'remark',
        'sarf_document_id',
        'activity_id',
    ];

    public function sarfDocument()
    {
        return $this->belongsTo(SarfDocument::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
