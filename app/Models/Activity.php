<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'code',
        'school_year_code',
        'branch_id',
        'level',
        'department',

        // SARF Detail
        'title',
        'description',
        'objectives',
        'type_of_activity',
        'event_type',
        'activity_level',        
        'participants_profile',  
        'participants_count',    
        'date_of_activity',
        'time_of_activity',
        'public_poster',
        'mode_of_conduct',
        'venue',
        'venue_type',            
        'platform',              

        // Budget
        'funds',
        'source',
        'amount',                
        'expected_collection',  
        'canteen',
        'procurement',
        'late_submission_reason',

        // Meta
        'received_by',
        'encoded_by',
        'status',

        // Signatory approvals
        'approval_dean_sa',
        'approval_avp_sps',
        'approval_dir_basic_ed',
        'approval_vp_acad',
        'approval_vp_hrd_legal',
        'approval_vp_comptroller',
        'approval_avp_finance',
        'remarks_dean_sa',
        'remarks_avp_sps',
        'remarks_dir_basic_ed',
        'remarks_vp_acad',
        'remarks_vp_hrd_legal',
        'remarks_vp_comptroller',
        'remarks_avp_finance',
    ];

    protected $casts = [
        'date_of_activity' => 'date',
        'department'       => 'array',
        'objectives'       => 'array',
        'level'            => 'array',
        'date_of_activity' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(\App\Models\Account::class, 'received_by');
    }

    public function encodedBy()
    {
        return $this->belongsTo(\App\Models\Account::class, 'encoded_by');
    }

    public function sarfDocuments()
    {
        return $this->hasMany(SarfDocument::class);
    }
}
