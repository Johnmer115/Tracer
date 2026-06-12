<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'code',
        'school_year_code',
        'branch_id',
        'level',
        'department',
        'organizations',

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
        'waiver_consent',
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
        'approval_auditing',
        'approval_comptroller_initial',
        'approval_finance_initial',
        'approval_osa_finance',
        'approval_finance_final',
        'approval_comptroller_final',
        'remarks_dean_sa',
        'remarks_avp_sps',
        'remarks_dir_basic_ed',
        'remarks_vp_acad',
        'remarks_vp_hrd_legal',
        'remarks_vp_comptroller',
        'remarks_avp_finance',
        'remarks_auditing',
        'remarks_comptroller_initial',
        'remarks_finance_initial',
        'remarks_osa_finance',
        'remarks_finance_final',
        'remarks_comptroller_final',
        'budget_dean_sa',
        'budget_avp_sps',
        'budget_dir_basic_ed',
        'budget_vp_acad',
        'budget_vp_hrd_legal',
        'budget_vp_comptroller',
        'budget_avp_finance',
        'budget_auditing',
        'budget_comptroller_initial',
        'budget_finance_initial',
        'budget_osa_finance',
        'budget_finance_final',
        'budget_comptroller_final',
        'approved_at_dean_sa',
        'approved_at_avp_sps',
        'approved_at_dir_basic_ed',
        'approved_at_vp_acad',
        'approved_at_vp_hrd_legal',
        'approved_at_auditing',
        'approved_at_comptroller_initial',
        'approved_at_finance_initial',
        'approved_at_osa_finance',
        'approved_at_finance_final',
        'approved_at_comptroller_final',

        // Rescheduling
        'reschedule_status',
        'reschedule_original_date',
        'reschedule_original_time',
        'reschedule_original_mode',
        'reschedule_original_venue',
        'reschedule_original_venue_type',
        'reschedule_original_platform',
        'reschedule_date',
        'reschedule_time',
        'reschedule_mode',
        'reschedule_venue',
        'reschedule_venue_type',
        'reschedule_platform',
        'reschedule_reason',
        'reschedule_remarks',
        'reschedule_requested_at',
        'reschedule_decided_at',
        'reschedule_approval_dean_sa',
        'reschedule_approval_avp_sps',
        'reschedule_approval_dir_basic_ed',
        'reschedule_approval_vp_acad',
        'reschedule_approval_vp_hrd_legal',
        'reschedule_approval_auditing',
        'reschedule_approval_comptroller_initial',
        'reschedule_approval_finance_initial',
        'reschedule_approval_osa_finance',
        'reschedule_approval_finance_final',
        'reschedule_approval_comptroller_final',
        'reschedule_remarks_dean_sa',
        'reschedule_remarks_avp_sps',
        'reschedule_remarks_dir_basic_ed',
        'reschedule_remarks_vp_acad',
        'reschedule_remarks_vp_hrd_legal',
        'reschedule_remarks_auditing',
        'reschedule_remarks_comptroller_initial',
        'reschedule_remarks_finance_initial',
        'reschedule_remarks_osa_finance',
        'reschedule_remarks_finance_final',
        'reschedule_remarks_comptroller_final',
        'reschedule_approved_at_dean_sa',
        'reschedule_approved_at_avp_sps',
        'reschedule_approved_at_dir_basic_ed',
        'reschedule_approved_at_vp_acad',
        'reschedule_approved_at_vp_hrd_legal',
        'reschedule_approved_at_auditing',
        'reschedule_approved_at_comptroller_initial',
        'reschedule_approved_at_finance_initial',
        'reschedule_approved_at_osa_finance',
        'reschedule_approved_at_finance_final',
        'reschedule_approved_at_comptroller_final',
        'reschedule_approved_by_dean_sa',
        'reschedule_approved_by_avp_sps',
        'reschedule_approved_by_dir_basic_ed',
        'reschedule_approved_by_vp_acad',
        'reschedule_approved_by_vp_hrd_legal',
        'reschedule_approved_by_auditing',
        'reschedule_approved_by_comptroller_initial',
        'reschedule_approved_by_finance_initial',
        'reschedule_approved_by_osa_finance',
        'reschedule_approved_by_finance_final',
        'reschedule_approved_by_comptroller_final',

        // Modification tracking (from Approval → Activity)
        'modification_type',       // null | revision | rescheduling
        'modification_remarks',    // admin notes about what to modify
    ];

    protected $casts = [
        'department'       => 'array',
        'organizations'    => 'array',
        'objectives'       => 'array',
        'level'            => 'array',
        'approved_at_dean_sa' => 'datetime',
        'approved_at_avp_sps' => 'datetime',
        'approved_at_dir_basic_ed' => 'datetime',
        'approved_at_vp_acad' => 'datetime',
        'approved_at_vp_hrd_legal' => 'datetime',
        'approved_at_auditing' => 'datetime',
        'approved_at_comptroller_initial' => 'datetime',
        'approved_at_finance_initial' => 'datetime',
        'approved_at_osa_finance' => 'datetime',
        'approved_at_finance_final' => 'datetime',
        'approved_at_comptroller_final' => 'datetime',
        'reschedule_original_date' => 'date',
        'reschedule_date' => 'date',
        'reschedule_requested_at' => 'datetime',
        'reschedule_decided_at' => 'datetime',
        'reschedule_approved_at_dean_sa' => 'datetime',
        'reschedule_approved_at_avp_sps' => 'datetime',
        'reschedule_approved_at_dir_basic_ed' => 'datetime',
        'reschedule_approved_at_vp_acad' => 'datetime',
        'reschedule_approved_at_vp_hrd_legal' => 'datetime',
        'reschedule_approved_at_auditing' => 'datetime',
        'reschedule_approved_at_comptroller_initial' => 'datetime',
        'reschedule_approved_at_finance_initial' => 'datetime',
        'reschedule_approved_at_osa_finance' => 'datetime',
        'reschedule_approved_at_finance_final' => 'datetime',
        'reschedule_approved_at_comptroller_final' => 'datetime',
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

    public function activityDateValues(): array
    {
        $value = $this->attributes['date_of_activity'] ?? null;

        if (! filled($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return collect($decoded)
                ->filter(fn ($date) => filled($date))
                ->values()
                ->all();
        }

        return collect(preg_split('/\s*(?:;|,|\R)\s*/', (string) $value))
            ->filter(fn ($date) => filled($date))
            ->values()
            ->all();
    }

    public function primaryActivityDate(): ?Carbon
    {
        $date = $this->activityDateValues()[0] ?? null;

        return filled($date) ? Carbon::parse($date) : null;
    }

    public function activityDateDisplay(string $format = 'M j, Y', string $separator = ', '): ?string
    {
        $dates = collect($this->activityDateValues())
            ->map(fn ($date) => Carbon::parse($date)->format($format))
            ->all();

        return $dates === [] ? null : implode($separator, $dates);
    }

    public function getDateOfActivityAttribute($value): ?ActivityDateValue
    {
        return filled($value) ? new ActivityDateValue($value) : null;
    }
}

class ActivityDateValue
{
    public function __construct(private string $value)
    {
    }

    public function values(): array
    {
        $decoded = json_decode($this->value, true);

        if (is_array($decoded)) {
            return collect($decoded)
                ->filter(fn ($date) => filled($date))
                ->values()
                ->all();
        }

        return collect(preg_split('/\s*(?:;|,|\R)\s*/', $this->value))
            ->filter(fn ($date) => filled($date))
            ->values()
            ->all();
    }

    public function first(): ?Carbon
    {
        $date = $this->values()[0] ?? null;

        return filled($date) ? Carbon::parse($date) : null;
    }

    public function format(string $format): string
    {
        return collect($this->values())
            ->map(fn ($date) => Carbon::parse($date)->format($format))
            ->implode(', ');
    }

    public function __toString(): string
    {
        return $this->values()[0] ?? '';
    }
}
