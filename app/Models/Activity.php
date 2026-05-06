<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    //
      protected $fillable = [
        'code',
        'school_year_code',
        'branch_id',
        'level',
        'department',
        'organization',
        'type_of_activity',
        'title',
        'date_of_activity',
        'time_of_activity',
        'venue',
        'participants',
        'description',
        'objectives',
        'mode_of_conduct',
        'public_poster',
        'event_type',
        'funds',
        'source',
        'canteen',
        'procurement',
        'received_by',
        'encoded_by',
        'status',

        // Approval signatories
        'approval_dean',
        'approval_avp_sps',
        'approval_director',
        'approval_vp_academic',
        'approval_vp_hrd',
        'approval_vp_auditor',
        'approval_comptroller',
        'approval_finance',

        // Per-approver remarks
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
    ];

        public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
