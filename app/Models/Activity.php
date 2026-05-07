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

        // Approvals (keep these if you still need them)
        'approval_dean',
        'approval_avp_sps',
        'approval_director',
        'approval_vp_academic',
        'approval_vp_hrd',
        'approval_vp_auditor',
        'approval_comptroller',
        'approval_finance',
        'remarks_dean',
        'remarks_avp_sps',
        'remarks_director',
        'remarks_vp_academic',
        'remarks_vp_hrd',
        'remarks_vp_auditor',
        'remarks_comptroller',
        'remarks_finance',
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