@extends('Dean_OSA.layouts.layout')

@section('title', 'Review Activity | SARF Tracking')
@section('page-title', 'Review Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <style>
    
    </style>
@endpush

@section('content')
<section class="panel" style="padding: 25px;">

    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @php
        $hasPendingReschedule = $activity->reschedule_status === 'pending';

        $statusClass = match($activity->status) {
            'pending'              => 'b-pending',
            'ongoing'              => 'b-ongoing',
            'for approval'         => 'b-for-approval',
            'for approval finance' => 'b-for-approval',
            'approved'             => 'b-approved',
            'completed'            => 'b-completed',
            'for revision'         => 'b-revision',
            'cancelled'            => 'b-cancelled',
            default                => 'b-pending',
        };

        $pipeline = [
            ['label' => 'Pending',      'val' => 'pending'],
            ['label' => 'Ongoing',      'val' => 'ongoing'],
            ['label' => 'For Approval', 'val' => 'for approval'],
            ['label' => 'Approved',     'val' => 'approved'],
        ];
        $pipeIdx = collect($pipeline)->search(fn($s) => $s['val'] === $activity->status) ?? -1;
        if ($activity->status === 'for approval finance') $pipeIdx = 2;
        if ($activity->status === 'completed') $pipeIdx = 3;

        $isApprovalUnlocked  = in_array($activity->status, ['for approval','for approval finance','approved','completed']);
        $isForFinance        = in_array($activity->status, ['for approval finance','approved','completed']);
        $isCompleted         = in_array($activity->status, ['approved','completed']);
        $showAdvancePopup    = in_array($activity->status, ['pending','ongoing']);

        // When reschedule is pending, Tab 2 is locked (approvals frozen)
        $isApprovalFrozen = $hasPendingReschedule;

        $signatories = [
            ['field' => 'approval_dean_sa',      'remark' => 'remarks_dean_sa',      'budget' => 'budget_dean_sa',      'role' => 'Dean for Student Affairs'],
            ['field' => 'approval_avp_sps',      'remark' => 'remarks_avp_sps',      'budget' => 'budget_avp_sps',      'role' => 'Assistant Vice President for Student Personnel Services'],
            ['field' => 'approval_dir_basic_ed', 'remark' => 'remarks_dir_basic_ed', 'budget' => 'budget_dir_basic_ed', 'role' => 'Director for Basic Education'],
            ['field' => 'approval_vp_acad',      'remark' => 'remarks_vp_acad',      'budget' => 'budget_vp_acad',      'role' => 'Vice President for Academic Affairs'],
            ['field' => 'approval_vp_hrd_legal', 'remark' => 'remarks_vp_hrd_legal', 'budget' => 'budget_vp_hrd_legal', 'role' => 'Vice President for HRD/Legal Director Division'],
        ];
        $financeSignatories = [
            ['field' => 'approval_auditing',            'remark' => 'remarks_auditing',            'budget' => 'budget_auditing',            'role' => 'Auditing'],
            ['field' => 'approval_comptroller_initial', 'remark' => 'remarks_comptroller_initial', 'budget' => 'budget_comptroller_initial', 'role' => 'Comptroller'],
            ['field' => 'approval_finance_initial',     'remark' => 'remarks_finance_initial',     'budget' => 'budget_finance_initial',     'role' => 'Finance'],
            ['field' => 'approval_osa_finance',         'remark' => 'remarks_osa_finance',         'budget' => 'budget_osa_finance',         'role' => 'OSA'],
            ['field' => 'approval_finance_final',       'remark' => 'remarks_finance_final',       'budget' => 'budget_finance_final',       'role' => 'Finance'],
            ['field' => 'approval_comptroller_final',   'remark' => 'remarks_comptroller_final',   'budget' => 'budget_comptroller_final',   'role' => 'Comptroller'],
        ];

        $mainFields    = array_column($signatories, 'field');
        $financeFields = array_column($financeSignatories, 'field');

        $levels     = is_array($activity->level)     ? $activity->level     : (filled($activity->level)     ? [$activity->level]     : []);
        $depts      = is_array($activity->department) ? $activity->department : (filled($activity->department) ? [$activity->department] : []);
        $orgs       = is_array($activity->organizations) ? $activity->organizations : (filled($activity->organizations) ? [$activity->organizations] : []);
        $objectives = is_array($activity->objectives) ? $activity->objectives : (filled($activity->objectives) ? [$activity->objectives] : []);

        $requiresBasicEdApproval = collect($levels)->contains(function ($level) {
            $level = Str::lower((string) $level);
            return Str::contains($level, ['elementary','junior high','senior high','basic','all levels']);
        });
        $requiresFinanceApproval = $activity->funds === 'With Budget';

        $applicableMainFields = collect($mainFields)
            ->reject(fn($f) => $f === 'approval_dir_basic_ed' && !$requiresBasicEdApproval)
            ->values()
            ->all();
        $applicableFinanceFields = $requiresFinanceApproval ? $financeFields : [];

        $mainLocked = [];
        foreach ($mainFields as $f) {
            $idx = array_search($f, $applicableMainFields, true);
            $mainLocked[$f] = $idx !== false && $idx > 0 && ($activity->{$applicableMainFields[$idx-1]} !== 'approved');
        }
        $financeLocked = [];
        foreach ($financeFields as $f) {
            $idx = array_search($f, $applicableFinanceFields, true);
            $financeLocked[$f] = $idx !== false && $idx > 0 && ($activity->{$applicableFinanceFields[$idx-1]} !== 'approved');
        }

        $approvalBadgeClass = fn($v) => match($v ?? 'pending') {
            'approved'    => 'b-approved',
            'disapproved' => 'b-disapproved',
            'for signature' => 'b-for-signature',
            default       => 'b-pending',
        };

        $allMainApproved    = collect($applicableMainFields)->every(fn($f) => $activity->{$f} === 'approved');
        $allFinanceApproved = collect($applicableFinanceFields)->every(fn($f) => $activity->{$f} === 'approved');

        $sarfLabels = [
            'A0'  => 'SARF Form',
            'A1'  => 'Budget Breakdown',
            'A2'  => 'Approved Budget Breakdown',
            'A3'  => 'Program Flow',
            'A4'  => 'Risk Management Plan',
            'A5'  => 'Summary List of Waiver / Consent & Medical',
            'A6'  => 'Reschedule of Activity',
            'A7'  => 'Acknowledgement Receipt',
            'A8'  => 'Canteen Slip',
            'A10' => 'Requested Materials',
        ];

        $approvalFields = array_merge($applicableMainFields, $applicableFinanceFields);
        $approvedCount  = collect($approvalFields)->filter(fn($f) => $activity->{$f} === 'approved')->count();
        $totalApprovals = count($approvalFields);
        $progressPct    = $totalApprovals > 0 ? round(($approvedCount / $totalApprovals) * 100) : 0;

        $docs = $activity->sarfDocuments->keyBy('type');
        $approvedSarfDoc = $docs->get('APPROVED_SARF');
        $approvedDocRemark = old('approved_remark')
            ?? ($approvedSarfDoc ? optional($approvedSarfDoc->remarks->sortByDesc('created_at')->first())->remark : null);

        $requestedTab = (int) request()->query('tab', 0);
        $activeTab = in_array($requestedTab, [1, 2, 3], true)
            ? $requestedTab
            : ($isCompleted ? 3 : ($isApprovalUnlocked ? 2 : 1));

        if ($activeTab === 3 && !$isCompleted) {
            $activeTab = $isApprovalUnlocked ? 2 : 1;
        }

        if ($activeTab === 2 && (!$isApprovalUnlocked || $isApprovalFrozen)) {
            $activeTab = 1;
        }
    @endphp

    {{-- ══════════════════════════════════════
         ADVANCE TO APPROVAL POPUP MODAL
    ══════════════════════════════════════ --}}
    @if($showAdvancePopup)
    <div id="advance-modal" style="display:none; position:fixed; inset:0;
        background:rgba(15,23,42,0.55); z-index:9999;
        align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:14px; padding:28px 28px 22px;
            width:100%; max-width:420px; margin:0 16px;
            box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#eff6ff;
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-stamp" style="color:#3b82f6; font-size:18px;"></i>
                </div>
                <div>
                    <p style="font-weight:700; font-size:15px; margin:0; color:#0f172a;">
                        Advance to For Approval?
                    </p>
                    <p style="font-size:12.5px; color:#64748b; margin:2px 0 0;">
                        This will unlock the approval workflow.
                    </p>
                </div>
            </div>
            <p style="font-size:13px; color:#475569; margin:0 0 20px; line-height:1.6;">
                You've reviewed the event details. Would you like to advance this SARF to
                <strong>For Approval</strong> status and begin the signatory workflow?
            </p>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="dismissAdvanceModal()" class="btn btn-filter">
                    Not Yet
                </button>
                <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    <input type="hidden" name="status" value="for approval">
                    <input type="hidden" name="current_tab" value="2">
                    <input type="hidden" name="focus" value="approval-workflow">
                    <button type="submit" class="btn btn-add">
                        <i class="fas fa-stamp"></i> Yes, Advance
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="panel">

        {{-- ── Panel Header ── --}}
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-alt"></i>
                {{ Str::limit($activity->title, 48) }}
                <span class="badge {{ $statusClass }}" style="margin-left:8px; font-size:11px;">
                    {{ ucfirst($activity->status) }}
                </span>
                @if($hasPendingReschedule)
                <span class="badge" style="margin-left:4px; font-size:11px; background:#fef3c7; color:#92400e; border:1px solid #fbbf24; padding:3px 10px; border-radius:20px;">
                    <i class="fas fa-calendar-alt" style="font-size:9px;"></i> Rescheduling
                </span>
                @endif
            </div>
            <div class="panel-controls">
                <div class="sarf-code-display sarf-code-display--header">
                    <span class="code-label">SARF Code</span>
                    <i class="fas fa-hashtag" style="color:#93c5fd; font-size:12px;"></i>
                    <span>{{ $activity->code }}</span>
                </div>
                <a href="{{ route('dean_osa.approval.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">

            {{-- ── Workflow Pipeline ── --}}
            @if(!in_array($activity->status, ['cancelled','for revision']))
            <div style="display:flex; align-items:center; margin-bottom:20px; padding:12px 18px;
                background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; overflow-x:auto;">
                @foreach($pipeline as $pi => $ps)
                    @php $done = $pi < $pipeIdx; $act = $pi === $pipeIdx; @endphp
                    <div style="display:flex; align-items:center; flex:1; min-width:60px;">
                        <div style="text-align:center; flex:1;">
                            <div style="width:28px; height:28px; border-radius:50%; margin:0 auto 4px;
                                display:flex; align-items:center; justify-content:center;
                                background:{{ $done ? '#dcfce7' : ($act ? '#3b82f6' : '#e2e8f0') }};
                                color:{{ $done ? '#15803d' : ($act ? '#fff' : '#94a3b8') }};
                                font-size:11px; font-weight:700;">
                                @if($done)<i class="fas fa-check"></i>@else{{ $pi + 1 }}@endif
                            </div>
                            <div style="font-size:10px; font-weight:{{ $act ? 700 : 500 }};
                                color:{{ $done ? '#15803d' : ($act ? '#3b82f6' : '#94a3b8') }};">
                                {{ $ps['label'] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="flex:0 0 14px; height:2px;
                                background:{{ $done ? '#86efac' : '#e2e8f0' }};"></div>
                        @endif
                    </div>
                @endforeach

                {{-- Rescheduling indicator in pipeline --}}
                @if($hasPendingReschedule)
                <div style="margin-left:12px; flex-shrink:0;">
                    <div style="display:flex; align-items:center; gap:6px; background:#fef3c7;
                        border:1.5px solid #fbbf24; border-radius:8px; padding:6px 14px;">
                        <i class="fas fa-pause-circle" style="color:#d97706; font-size:14px; animation:pulse 1.5s infinite;"></i>
                        <span style="font-size:11px; font-weight:700; color:#92400e;">RESCHEDULING</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ── Workflow Quick Actions ── --}}
            @if(!in_array($activity->status, ['approved','cancelled']))
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; align-items:center;">
                @if($activity->status === 'ongoing')
                    <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="for approval">
                        <input type="hidden" name="current_tab" value="2">
                        <input type="hidden" name="focus" value="approval-workflow">
                        <button type="submit" class="btn-quick-action btn-quick-action--blue">
                            <i class="fas fa-stamp"></i> Advance to For Approval
                        </button>
                    </form>
                @endif
                @if($activity->status === 'for revision')
                    <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="ongoing">
                        <input type="hidden" name="current_tab" value="1">
                        <button class="btn btn-add">
                            <i class="fas fa-redo"></i> Reopen as Ongoing
                        </button>
                    </form>
                @endif
                @if(in_array($activity->status, ['ongoing','for approval','for approval finance']))
                    <div class="workflow-spacer" style="flex:1;"></div>
                    <div class="workflow-side-actions">
                        @if(!$hasPendingReschedule)
                            <button type="button" class="btn-quick-action btn-quick-action--mod"
                                onclick="openModificationModal({{ $activity->id }}, '{{ addslashes($activity->code) }}')">
                                <i class="ti ti-adjustments-horizontal"></i> Request Modification
                            </button>
                        @endif
                        <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST"
                            onsubmit="return confirm('Cancel this activity?');">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <input type="hidden" name="current_tab" value="1">
                            <button type="submit" class="btn-quick-action btn-quick-action--danger">
                                <i class="fas fa-times"></i> Cancel Activity
                            </button>
                        </form>
                    </div>
                @endif
            </div>
            @endif

            {{-- ══════════════════════════════════════
                 RESCHEDULE PENDING BANNER
            ══════════════════════════════════════ --}}
            @php $hasPendingReschedule = $activity->reschedule_status === 'pending'; @endphp

            @if($hasPendingReschedule)
            <div class="resched-banner" onclick="openRescheduleReviewModal()" style="cursor:pointer;">
                <div class="resched-banner-icon">
                    <i class="fas fa-calendar-exclamation"></i>
                </div>
                <div class="resched-banner-content">
                    <div class="resched-banner-title">
                        <i class="fas fa-pause-circle" style="animation:pulse 1.5s infinite;"></i>
                        Reschedule Pending — Approvals Paused
                    </div>
                    <div class="resched-banner-desc">
                        A rescheduling modification has been submitted. Click here to review the schedule changes and approve or reject.
                    </div>
                </div>
                <div class="resched-banner-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════
                 STEP INDICATORS — blue
            ══════════════════════════════════════ --}}
            <div class="step-indicators">
                <button type="button" id="step-indicator-1"
                    class="step-indicator-btn {{ $isApprovalUnlocked ? 'completed' : '' }} {{ $activeTab === 1 ? 'active' : '' }}"
                    onclick="showTab(1)">
                    <i class="fas fa-info-circle"></i> 1. Event Details
                </button>
                <button type="button" id="step-indicator-2"
                    class="step-indicator-btn {{ $isCompleted ? 'completed' : '' }} {{ (!$isApprovalUnlocked || $isApprovalFrozen) ? 'step-locked' : '' }} {{ $activeTab === 2 ? 'active' : '' }}"
                    onclick="{{ ($isApprovalUnlocked && !$isApprovalFrozen) ? 'showTab(2)' : 'return false' }}"
                    title="{{ $isApprovalFrozen ? 'Approvals frozen — resolve the pending reschedule first.' : (!$isApprovalUnlocked ? 'Advance to For Approval status first.' : '') }}">
                    @if($isApprovalFrozen)
                        <i class="fas fa-pause-circle" style="font-size:10px; color:#d97706;"></i>
                    @elseif(!$isApprovalUnlocked)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-stamp"></i>
                    @endif
                    2. Approval
                </button>
                <button type="button" id="step-indicator-3"
                    class="step-indicator-btn {{ !$isCompleted ? 'step-locked' : '' }} {{ $activeTab === 3 ? 'active' : '' }}"
                    onclick="{{ $isCompleted ? 'showTab(3)' : 'return false' }}"
                    title="{{ !$isCompleted ? 'Available once all approvals are approved.' : '' }}">
                    @if(!$isCompleted)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-check-double"></i>
                    @endif
                    3. Approved SARF
                </button>
            </div>

            {{-- ══════════════════════════════════════
                 TAB 1 — EVENT DETAILS
                 Uses show-section/show-grid/show-field
                 identical to activity/show.blade.php
            ══════════════════════════════════════ --}}
            <div id="tab-1" style="{{ $activeTab === 1 ? '' : 'display:none;' }}">

                {{-- Organizational Context --}}
                <div class="show-section">
                    <div class="show-section-header">
                        <i class="fas fa-sitemap"></i> Organizational Context
                    </div>
                    <div class="show-grid">

                        <div class="show-field">
                            <div class="show-label">Branch</div>
                            <div class="show-value">{{ $activity->branch->name ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">School Year</div>
                            <div class="show-value">{{ $activity->school_year_code ?? '—' }}</div>
                        </div>

                        <div class="show-field full">
                            <div class="show-label">Level(s)</div>
                            @if(count($levels))
                                <div class="tag-display">
                                    @foreach($levels as $lvl)
                                        <span class="tag">{{ $lvl }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="show-value muted">Not specified</div>
                            @endif
                        </div>

                        <div class="show-field full">
                            <div class="show-label">Department(s)</div>
                            @if(count($depts))
                                <div class="tag-display">
                                    @foreach($depts as $dept)
                                        <span class="tag purple">{{ $dept }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="show-value muted">Not specified</div>
                            @endif
                        </div>

                        <div class="show-field full">
                            <div class="show-label">Organization(s)</div>
                            @if(count($orgs))
                                <div class="tag-display">
                                    @foreach($orgs as $org)
                                        <span class="tag green">{{ $org }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="show-value muted">Not specified</div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Activity Information --}}
                <div class="show-section">
                    <div class="show-section-header purple">
                        <i class="fas fa-calendar-alt"></i> Activity Information
                    </div>
                    <div class="show-grid">

                        <div class="show-field full">
                            <div class="show-label">Activity Title</div>
                            <div class="show-value" style="font-size:15px; font-weight:700; color:#0f172a;">
                                {{ $activity->title ?? '—' }}
                            </div>
                        </div>

                        <div class="show-field full">
                            <div class="show-label">Short Description</div>
                            <div class="show-value">{{ $activity->description ?: '—' }}</div>
                        </div>

                        <div class="show-field full">
                            <div class="show-label">Objectives</div>
                            @if(count($objectives))
                                <ul class="obj-display">
                                    @foreach($objectives as $obj)
                                        <li>{{ $obj }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="show-value muted">None listed</div>
                            @endif
                        </div>

                        <div class="show-field">
                            <div class="show-label">Type of Activity</div>
                            <div class="show-value">{{ $activity->type_of_activity ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Event Type</div>
                            <div class="show-value">{{ $activity->event_type ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Level of Activity</div>
                            <div class="show-value">{{ $activity->activity_level ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Public Poster</div>
                            <div class="show-value">{{ $activity->public_poster ?? '—' }}</div>
                        </div>

                    </div>
                </div>

                {{-- Schedule, Conduct & Extras --}}
                <div class="show-section">
                    <div class="show-section-header green">
                        <i class="fas fa-clock"></i> Schedule, Conduct & Extras
                    </div>
                    <div class="show-grid">

                        <div class="show-field">
                            <div class="show-label">Date of Activity</div>
                            <div class="show-value">
                                {{ $activity->date_of_activity?->format('F j, Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Time of Activity</div>
                            <div class="show-value">{{ $activity->time_of_activity ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Mode of Conduct</div>
                            <div class="show-value">{{ $activity->mode_of_conduct ?? '—' }}</div>
                        </div>

                        @if(in_array($activity->mode_of_conduct, ['Face to Face','Hybrid']))
                        <div class="show-field">
                            <div class="show-label">Venue</div>
                            <div class="show-value">
                                {{ $activity->venue ?? '—' }}
                                @if($activity->venue_type)
                                    <span class="inline-tag">{{ $activity->venue_type }}</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(in_array($activity->mode_of_conduct, ['Online','Hybrid']))
                        <div class="show-field">
                            <div class="show-label">Platform</div>
                            <div class="show-value">
                                <i class="fas fa-video" style="color:#3b82f6; font-size:12px;"></i>
                                {{ $activity->platform ?? '—' }}
                            </div>
                        </div>
                        @endif

                        <div class="show-field">
                            <div class="show-label">Number of Participants</div>
                            <div class="show-value">
                                {{ $activity->participants_count ? number_format($activity->participants_count) : '—' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Participant Profile</div>
                            <div class="show-value">{{ $activity->participants_profile ?? '—' }}</div>
                        </div>


                    </div>
                </div>

                {{-- Budgetary Requirements --}}
                <div class="show-section">
                    <div class="show-section-header amber">
                        <i class="fas fa-coins"></i> Budgetary Requirements
                    </div>
                    <div class="show-grid">

                        <div class="show-field">
                            <div class="show-label">Funds</div>
                            <div class="show-value">{{ $activity->funds ?? '—' }}</div>
                        </div>

                        @if($activity->funds === 'With Budget' && $activity->source)
                        <div class="show-field">
                            <div class="show-label">Source</div>
                            <div class="show-value">{{ $activity->source }}</div>
                        </div>
                        @endif

                        @if(in_array($activity->funds, ['With Budget', 'ATC']))
                        <div class="show-field">
                            <div class="show-label">Amount</div>
                            <div class="show-value amount-green">
                                {{ $activity->amount ? '₱ ' . number_format($activity->amount, 2) : '—' }}
                            </div>
                        </div>
                        @endif

                        @if($activity->funds === 'ATC')
                        <div class="show-field">
                            <div class="show-label">Expected Collection</div>
                            <div class="show-value amount-amber">
                                {{ $activity->expected_collection
                                    ? '₱ ' . number_format($activity->expected_collection, 2)
                                    : '—' }}
                            </div>
                        </div>
                        @endif

                        @if(in_array($activity->funds, ['With Budget','ATC']))
                        <div class="show-field">
                            <div class="show-label">Canteen</div>
                            <div class="show-value">{{ $activity->canteen ?? '—' }}</div>
                        </div>
                        <div class="show-field">
                            <div class="show-label">Procurement</div>
                            <div class="show-value">{{ $activity->procurement ?? '—' }}</div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- Late submission notice --}}
                @if(filled($activity->late_submission_reason ?? null))
                    <div class="late-notice">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Late Submission</strong><br>
                            {{ $activity->late_submission_reason }}
                        </div>
                    </div>
                @endif

                {{-- Submission Info --}}
                <div class="show-section">
                    <div class="show-section-header">
                        <i class="fas fa-user-check"></i> Submission Info
                    </div>
                    <div class="show-grid">

                        <div class="show-field">
                            <div class="show-label">Received By</div>
                            <div class="show-value">
                                <i class="fas fa-user" style="color:#3b82f6; font-size:12px;"></i>
                                {{ $activity->receivedBy->username ?? '-' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Encoded By</div>
                            <div class="show-value">
                                <i class="fas fa-user" style="color:#3b82f6; font-size:12px;"></i>
                                {{ $activity->encodedBy->username ?? '-' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Submitted</div>
                            <div class="show-value">
                                {{ $activity->created_at?->format('F j, Y · g:i A') ?? '—' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Last Updated</div>
                            <div class="show-value">
                                {{ $activity->updated_at?->format('F j, Y · g:i A') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attached Documents --}}
                <div class="show-section">
                    <div class="show-section-header">
                        <i class="fas fa-paperclip"></i> Attachment Files
                        <span style="margin-left:auto; font-size:12px; font-weight:400; color:#64748b;">
                            {{ $docs->count() }} of {{ count($sarfLabels) }} types attached
                        </span>
                        @if($docs->isNotEmpty())
                            <a href="{{ route('dean_osa.sarf-documents.print-activity', $activity) }}"
                                target="_blank" class="attachment-view-btn">
                                <i class="fas fa-print"></i> Print All
                            </a>
                        @endif
                    </div>
                    @if($docs->isEmpty())
                        <div style="padding:20px; text-align:center; color:#94a3b8; font-style:italic; font-size:13px;">
                            <i class="fas fa-folder-open"></i> No attachments uploaded yet.
                        </div>
                    @else
                        @foreach($sarfLabels as $type => $label)
                            @if($docs->has($type))
                                <div class="attachment-view-row">
                                    <div class="attachment-view-left">
                                        <span class="sarf-badge">{{ $type }}</span>
                                        <div>
                                            <div class="td-main">{{ $label }}</div>
                                            <div class="td-sub">
                                                <i class="fas fa-file-pdf" style="color:#ef4444;"></i>
                                                {{ $docs[$type]->original_filename }}
                                                &nbsp;·&nbsp; {{ $docs[$type]->created_at?->format('M j, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a href="{{ route('dean_osa.sarf-documents.show', $docs[$type]) }}"
                                            target="_blank" class="attachment-view-btn">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Scroll sentinel --}}
                @if($showAdvancePopup)
                    <div id="scroll-sentinel" style="height:1px; margin-top:8px;"></div>
                @endif

                <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                    @if($isApprovalUnlocked && !$isApprovalFrozen)
                        <button type="button" onclick="showTab(2)" class="btn btn-add">
                            Approval <i class="fas fa-arrow-right"></i>
                        </button>
                    @elseif($isApprovalFrozen)
                        <button type="button" class="btn btn-filter" disabled title="Resolve the pending reschedule first.">
                            <i class="fas fa-pause-circle"></i> Approval Paused
                        </button>
                    @endif
                </div>

            </div>{{-- /tab-1 --}}


            {{-- ══════════════════════════════════════
                 TAB 2 — APPROVAL
            ══════════════════════════════════════ --}}
            <div id="tab-2" style="{{ $activeTab === 2 ? '' : 'display:none;' }}">
                <div id="approval-workflow"></div>

                @if($hasPendingReschedule)
                    <div class="notice-card notice-card--warn">
                        <i class="fas fa-pause-circle"></i>
                        <div>
                            <strong>Approvals Paused</strong> — A reschedule request is pending review.
                            All signatory approvals are frozen until the reschedule is approved or rejected.
                            Scroll up to review the reschedule request.
                        </div>
                    </div>
                @endif

                @if(!$isApprovalUnlocked)
                    <div class="notice-card notice-card--blue">
                        <i class="fas fa-lock"></i>
                        Advance the activity to <strong>For Approval</strong> status first.
                    </div>
                @endif

                @if($isCompleted)
                    <div class="notice-card notice-card--success">
                        <i class="fas fa-check-circle"></i>
                        <strong>All approvals complete.</strong> This SARF is fully approved.
                    </div>
                @endif

                {{-- Main Signatories --}}
                <div class="show-section">
                    <div class="show-section-header">
                        <i class="fas fa-stamp"></i> Signatory Approvals
                        @if($allMainApproved)
                            <span class="mini-pill pill-green" style="margin-left:auto;">
                                <i class="fas fa-check"></i> All Approved
                            </span>
                        @endif
                    </div>
                    <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
                        @foreach($signatories as $sig)
                            @php
                                $skipped = !in_array($sig['field'], $applicableMainFields, true);
                                $locked  = !$skipped && ($mainLocked[$sig['field']] || !$isApprovalUnlocked || $isApprovalFrozen);
                                $current = $activity->{$sig['field']} ?? 'pending';
                            @endphp
                            @continue($skipped)
                            <div id="approval-card-{{ $sig['field'] }}" class="signatory-card {{ ($locked || $skipped) ? 'signatory-card--locked' : '' }}">
                                <div class="signatory-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fas {{ $locked ? 'fa-lock' : 'fa-user-tie' }}"
                                            style="color:{{ $locked ? '#94a3b8' : '#3b82f6' }}; font-size:12px;"></i>
                                        <span style="font-weight:600; font-size:13.5px; color:#1e293b;">
                                            {{ $sig['role'] }}
                                        </span>
                                    </div>
                                    <span class="badge approval-status-badge {{ $approvalBadgeClass($current) }}">
                                        {{ ucfirst($current) }}
                                    </span>
                                </div>
                                @if($activity->{$sig['remark']})
                                    <div class="signatory-remark">
                                        <i class="fas fa-comment-dots" style="color:#d97706;"></i>
                                        {{ $activity->{$sig['remark']} }}
                                    </div>
                                @endif
                               @if($activity->{$sig['budget']} !== null)
                                    <div class="approved-budget-box">
                                        <div class="approved-budget-label">
                                            <i class="fas fa-wallet"></i>
                                            Approved Budget
                                        </div>

                                        <div class="approved-budget-value">
                                            &#8369; {{ number_format($activity->{$sig['budget']}, 2) }}
                                        </div>
                                    </div>
                                @endif
                                <div class="signatory-body">
                                    @if($locked)
                                        <span class="td-muted" style="font-size:12px;">
                                            <i class="fas fa-lock"></i>
                                            {{ $isApprovalFrozen
                                                ? 'Approvals are paused while the reschedule request is pending.'
                                                : (!$isApprovalUnlocked
                                                    ? 'Unlock by advancing status to For Approval.'
                                                    : 'Waiting for previous signatory.') }}
                                        </span>
                                    @else
                                        <form action="{{ route('dean_osa.approval.approve', $activity->id) }}"
                                            method="POST"
                                            class="approval-row-form">
                                            @csrf
                                            <input type="hidden" name="approver" value="{{ $sig['field'] }}">
                                            <input type="hidden" name="current_tab" value="2">
                                            <select name="status" class="filter-select">
                                                <option value="pending"     @selected($current==='pending')>Pending</option>
                                                <option value="for signature" @selected($current==='for signature')>For Signature</option>
                                                <option value="approved"    @selected($current==='approved')>Approved</option>
                                                <option value="disapproved" @selected($current==='disapproved')>Disapproved</option>
                                            </select>
                                            <input type="text" name="remark" class="form-control approval-remark-input"
                                                placeholder="Remark (optional)"
                                                value="{{ $activity->{$sig['remark']} ?? '' }}">
                                            @if(in_array($activity->funds, ['With Budget', 'ATC']) && $activity->amount !== null)
                                                <span class="approval-budget-title">
                                                    <i class="fas fa-wallet"></i> Approved Budget
                                                </span>
                                                <input type="number" name="approved_budget"
                                                    class="form-control approved-budget-input"
                                                    step="0.01" min="0"
                                                    placeholder="Approved budget"
                                                    value="{{ $activity->{$sig['budget']} ?? $activity->amount }}">
                                            @endif
                                            <button type="submit" class="btn btn-add" style="font-size:12px;">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Finance Signatories --}}
                @if($requiresFinanceApproval)
                <div class="show-section" style="{{ !$isForFinance ? 'opacity:0.6;' : '' }}">
                    <div class="show-section-header green">
                        <i class="fas fa-file-invoice-dollar"></i> Finance Approvals
                        @if(!$isForFinance)
                            <span style="margin-left:auto; font-size:11px; color:#94a3b8; font-weight:600;">
                                <i class="fas fa-lock"></i> Unlocks after all signatories above approve
                            </span>
                        @elseif($allFinanceApproved)
                            <span class="mini-pill pill-green" style="margin-left:auto;">
                                <i class="fas fa-check"></i> All Approved
                            </span>
                        @endif
                    </div>
                    <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
                        @foreach($financeSignatories as $sig)
                            @php
                                $skipped = !in_array($sig['field'], $applicableFinanceFields, true);
                                $locked  = !$skipped && ($financeLocked[$sig['field']] || !$isForFinance || $isApprovalFrozen);
                                $current = $activity->{$sig['field']} ?? 'pending';
                            @endphp
                            @continue($skipped)
                            <div id="approval-card-{{ $sig['field'] }}" class="signatory-card {{ ($locked || $skipped) ? 'signatory-card--locked' : '' }}">
                                <div class="signatory-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fas {{ $locked ? 'fa-lock' : 'fa-user-tie' }}"
                                            style="color:{{ $locked ? '#94a3b8' : '#16a34a' }}; font-size:12px;"></i>
                                        <span style="font-weight:600; font-size:13.5px; color:#1e293b;">
                                            {{ $sig['role'] }}
                                        </span>
                                    </div>
                                    <span class="badge approval-status-badge {{ $approvalBadgeClass($current) }}">
                                        {{ ucfirst($current) }}
                                    </span>
                                </div>
                                @if($activity->{$sig['remark']})
                                    <div class="signatory-remark">
                                        <i class="fas fa-comment-dots" style="color:#d97706;"></i>
                                        {{ $activity->{$sig['remark']} }}
                                    </div>
                                @endif
                                @if($activity->{$sig['budget']} !== null)
                                    <div class="signatory-remark" style="background:#f0fdf4; border-bottom-color:#bbf7d0; color:#15803d;">
                                        <i class="fas fa-coins" style="color:#16a34a;"></i>
                                        Approved budget: &#8369; {{ number_format($activity->{$sig['budget']}, 2) }}
                                    </div>
                                @endif
                                <div class="signatory-body">
                                    @if($locked)
                                        <span class="td-muted" style="font-size:12px;">
                                            <i class="fas fa-lock"></i>
                                            {{ $isApprovalFrozen
                                                ? 'Approvals are paused while the reschedule request is pending.'
                                                : (!$isForFinance
                                                    ? 'Unlocks after all signatories are approved.'
                                                    : 'Waiting for previous signatory.') }}
                                        </span>
                                    @else
                                        <form action="{{ route('dean_osa.approval.approve', $activity->id) }}"
                                            method="POST"
                                            class="approval-row-form">
                                            @csrf
                                            <input type="hidden" name="approver" value="{{ $sig['field'] }}">
                                            <input type="hidden" name="current_tab" value="2">
                                            <select name="status" class="filter-select">
                                                <option value="pending"     @selected($current==='pending')>Pending</option>
                                                <option value="for signature" @selected($current==='for signature')>For Signature</option>
                                                <option value="approved"    @selected($current==='approved')>Approved</option>
                                                <option value="disapproved" @selected($current==='disapproved')>Disapproved</option>
                                            </select>
                                            <input type="text" name="remark" class="form-control approval-remark-input"
                                                placeholder="Remark (optional)"
                                                value="{{ $activity->{$sig['remark']} ?? '' }}">
                                            @if(in_array($activity->funds, ['With Budget', 'ATC']) && $activity->amount !== null)
                                                <span class="approval-budget-title">
                                                    <i class="fas fa-wallet"></i> Budget
                                                </span>
                                                <input type="number" name="approved_budget"
                                                    class="form-control approved-budget-input"
                                                    step="0.01" min="0"
                                                    placeholder="Approved budget"
                                                    value="{{ $activity->{$sig['budget']} ?? $activity->amount }}">
                                            @endif
                                            <button type="submit" class="btn btn-add" style="font-size:12px;">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="display:flex; justify-content:space-between; margin-top:20px;">
                    <button type="button" onclick="showTab(1)" class="btn btn-filter">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    @if($isCompleted)
                        <button type="button" onclick="showTab(3)" class="btn btn-add">
                            Approved SARF <i class="fas fa-arrow-right"></i>
                        </button>
                    @endif
                </div>

            </div>{{-- /tab-2 --}}


            {{-- ══════════════════════════════════════
                 TAB 3 — APPROVED SARF
            ══════════════════════════════════════ --}}
            <div id="tab-3" style="{{ $activeTab === 3 ? '' : 'display:none;' }}">

                @if(!$isCompleted)
                    <div class="notice-card notice-card--blue">
                        <i class="fas fa-lock"></i>
                        This section is available once all approvals are completed.
                    </div>
                @else
                    <div class="notice-card notice-card--success">
                        <i class="fas fa-check-circle"></i>
                        <strong>SARF Fully Approved.</strong>
                        All signatories have approved this activity.
                    </div>

                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background:#fff; padding:20px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                            <div style="width:32px; height:32px; border-radius:8px; background:#eff6ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fas fa-file-pdf" style="color:#3b82f6; font-size:14px;"></i>
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:13.5px; color:#0f172a;">Approved SARF</div>
                                <div style="font-size:12px; color:#94a3b8;">
                                    @if($approvedSarfDoc)
                                        {{ $approvedSarfDoc->original_filename }}
                                    @else
                                        No file uploaded yet
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('dean_osa.approval.document.store', $activity->id) }}"
                            method="POST" enctype="multipart/form-data"
                            style="display:flex; flex-direction:column; gap:16px;">
                            @csrf
                            <input type="hidden" name="current_tab" value="3">

                            <label class="approved-dropzone is-visible" for="approved_sarf_file">
                                <input type="file" name="approved_sarf_file"
                                    id="approved_sarf_file" accept=".pdf"
                                    onchange="updateApprovedFileName('sarf', this)">
                                <span class="approved-dropzone-inner">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="approved-dropzone-main">Choose a file or drag and drop it here</span>
                                    <span class="approved-dropzone-sub">PDF format, up to 10MB</span>
                                    <span class="approved-file-chip">
                                        <i class="fas fa-file-pdf"></i>
                                        <span id="approved_fname_sarf">
                                            @if($approvedSarfDoc)
                                                {{ $approvedSarfDoc->original_filename }}
                                            @else
                                                No file chosen
                                            @endif
                                        </span>
                                    </span>
                                </span>
                            </label>

                            <div class="document-check-row">
                                <a href="#"
                                    target="_blank"
                                    class="document-check-btn document-preview-btn"
                                    id="preview_btn_sarf">
                                    <i class="fas fa-eye"></i> Preview Selected File
                                </a>
                                @if($approvedSarfDoc)
                                    <a href="{{ route('dean_osa.sarf-documents.show', $approvedSarfDoc) }}"
                                        target="_blank" class="document-check-btn">
                                        <i class="fas fa-file-pdf"></i> View Document
                                    </a>
                                    <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $approvedSarfDoc, 'download' => 1]) }}"
                                        class="document-check-btn document-download-btn">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                @endif
                            </div>

                            @error('approved_sarf_file')
                                <div style="color:#b91c1c; font-size:12px;">{{ $message }}</div>
                            @enderror

                            <div class="approved-remark-box">
                                <label for="approved_remark" style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:8px;">
                                    Remark for approved SARF
                                </label>
                                <textarea id="approved_remark" name="approved_remark"
                                    placeholder="Add a remark for the approved SARF...">{{ $approvedDocRemark }}</textarea>
                                @error('approved_remark')
                                    <div style="margin-top:8px; color:#b91c1c; font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="display:flex; justify-content:flex-end;">
                                <button type="submit" class="btn btn-add" style="font-size:12px;">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </form>
                        @error('approved_doc')
                            <div style="color:#b91c1c; font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div style="display:flex; justify-content:flex-start; margin-top:20px;">
                    <button type="button" onclick="showTab(2)" class="btn btn-filter">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>

            </div>{{-- /tab-3 --}}

        </div>{{-- /padding --}}
    </div>{{-- /panel --}}
</section>

<script>
/* ── Tab navigation ── */
const TOTAL_TABS = 3;
const INITIAL_TAB = @json($activeTab);

function showTab(n, options = {}) {
    for (let i = 1; i <= TOTAL_TABS; i++) {
        const pane = document.getElementById('tab-' + i);
        if (pane) pane.style.display = (i === n) ? 'block' : 'none';
        const btn = document.getElementById('step-indicator-' + i);
        if (btn) btn.classList.toggle('active', i === n);
    }
    if (options.scroll !== false) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function scrollToFocusTarget(targetId) {
    if (!targetId || !/^[A-Za-z0-9_-]+$/.test(targetId)) return;

    const target = document.getElementById(targetId);
    if (!target) return;

    setTimeout(() => {
        const y = target.getBoundingClientRect().top + window.pageYOffset - 90;
        window.scrollTo({ top: Math.max(y, 0), behavior: 'smooth' });
    }, 80);
}

function toggleApprovedFile(type, checked) {
    const wrap = document.getElementById('approved-upload-wrap-' + type);
    const card = document.getElementById('approved-card-' + type);
    if (wrap) wrap.classList.toggle('is-visible', checked);
    if (card) card.classList.toggle('is-selected', checked);
}

function updateApprovedFileName(type, input) {
    const display = document.getElementById('approved_fname_' + type);
    const preview = document.getElementById('preview_btn_' + type);

    if (!input?.files?.length) {
        if (display) display.textContent = 'No file chosen';
        if (preview) {
            preview.classList.remove('is-visible');
            preview.removeAttribute('href');
        }
        return;
    }

    const file = input.files[0];
    if (display) display.textContent = file.name;
    if (preview) {
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        const objectUrl = URL.createObjectURL(file);
        preview.href = objectUrl;
        preview.dataset.objectUrl = objectUrl;
        preview.classList.add('is-visible');
    }
}

function setupApprovedDropzones() {
    document.querySelectorAll('.approved-dropzone').forEach((dropzone) => {
        const input = dropzone.querySelector('input[type="file"]');
        if (!input) return;

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.style.borderColor = '#3b82f6';
                dropzone.style.background = '#eff6ff';
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.style.background = '#fff';
            });
        });

        dropzone.addEventListener('drop', (event) => {
            if (!event.dataTransfer?.files?.length) return;
            input.files = event.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const tabFromUrl = Number(params.get('tab'));
    const focusTarget = params.get('focus');

    if ([1, 2, 3].includes(tabFromUrl)) {
        showTab(tabFromUrl, { scroll: !focusTarget });
    } else {
        showTab(INITIAL_TAB, { scroll: false });
    }

    scrollToFocusTarget(focusTarget);
    setupApprovedDropzones();
});

/* ── Scroll-triggered advance popup ── */
@if($showAdvancePopup)
let popupDismissed = false;

function dismissAdvanceModal() {
    document.getElementById('advance-modal').style.display = 'none';
    popupDismissed = true;
    setTimeout(() => { popupDismissed = false; }, 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    const sentinel = document.getElementById('scroll-sentinel');
    if (!sentinel) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !popupDismissed) {
                document.getElementById('advance-modal').style.display = 'flex';
            }
        });
    }, { threshold: 0.5 });

    observer.observe(sentinel);

    document.getElementById('advance-modal').addEventListener('click', function(e) {
        if (e.target === this) dismissAdvanceModal();
    });
});
@endif

/* ── Reschedule form toggle (kept for backward compat) ── */
function toggleRescheduleForm() {
    const panel = document.getElementById('reschedule-form-panel');
    if (panel) {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        if (panel.style.display === 'block') {
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

/* ── Reschedule Review Modal ── */
function openRescheduleReviewModal() {
    document.getElementById('reschedReviewOverlay').classList.add('active');
}
function closeRescheduleReviewModal() {
    document.getElementById('reschedReviewOverlay').classList.remove('active');
}

// Escape key for reschedule modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeRescheduleReviewModal();
    }
});
</script>

{{-- ══════════════════════════════════════════════
     MODIFICATION MODAL (show page)
══════════════════════════════════════════════ --}}
<div class="mod-overlay" id="modOverlay" onclick="closeModificationModal()">
    <div class="mod-modal" onclick="event.stopPropagation()">
        <div class="mod-modal-header">
            <div class="mod-modal-icon">
                <i class="ti ti-adjustments-horizontal"></i>
            </div>
            <div>
                <h3 class="mod-modal-title">Request Modification</h3>
                <p class="mod-modal-subtitle" id="modSubtitle">SARF Code: —</p>
            </div>
            <button type="button" class="mod-close" onclick="closeModificationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="modForm" method="POST" action="">
            @csrf

            <div class="mod-modal-body">
                <p class="mod-label">What type of modification?</p>

                <div class="mod-type-cards">
                    <label class="mod-type-card">
                        <input type="radio" name="modification_type" value="revision" required
                            onchange="selectModType(this.value)">
                        <div class="mod-type-card-inner">
                            <div class="mod-type-icon" style="background:#dbeafe; color:#1d4ed8;">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="mod-type-label">Revision</div>
                            <div class="mod-type-desc">
                                Send back for content edits.<br>
                                Activity returns to approval after changes.
                            </div>
                        </div>
                    </label>

                    <label class="mod-type-card">
                        <input type="radio" name="modification_type" value="rescheduling" required
                            onchange="selectModType(this.value)">
                        <div class="mod-type-card-inner">
                            <div class="mod-type-icon" style="background:#fef3c7; color:#92400e;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="mod-type-label">Rescheduling</div>
                            <div class="mod-type-desc">
                                Change schedule details.<br>
                                Requires schedule approval before returning.
                            </div>
                        </div>
                    </label>
                </div>

                <div class="mod-remarks-wrap">
                    <label class="mod-label" for="modRemarks">Remarks / Instructions <span style="color:#94a3b8; font-weight:400;">(optional)</span></label>
                    <textarea name="modification_remarks" id="modRemarks" class="form-control"
                        rows="3" maxlength="1000"
                        placeholder="Describe what needs to be modified…"></textarea>
                </div>
            </div>

            <div class="mod-modal-footer">
                <button type="button" class="btn btn-filter" onclick="closeModificationModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-add" id="modSubmitBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Send for Modification
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Modification button (quick action bar) ── */
.btn-quick-action--mod {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    color: #b45309;
    border-color: #fcd34d;
}
.btn-quick-action--mod:hover {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
    color: #92400e;
    box-shadow: 0 3px 10px rgba(217,119,6,0.15);
}

/* ══════════════════════════════════════════════
   MODIFICATION MODAL
══════════════════════════════════════════════ */
.mod-overlay {
    display:none;
    position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,0.55);
    backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
    animation:modFadeIn .2s ease;
}
.mod-overlay.active { display:flex; }

@keyframes modFadeIn  { from { opacity:0; } to { opacity:1; } }
@keyframes modSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

.mod-modal {
    background:#fff;
    border-radius:16px;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    width:520px; max-width:94vw;
    overflow:hidden;
    animation:modSlideUp .25s ease;
}
.mod-modal-header {
    display:flex; align-items:center; gap:14px;
    padding:20px 24px;
    background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%);
    border-bottom:1px solid #e0e7ff;
    position:relative;
}
.mod-modal-icon {
    width:44px; height:44px; border-radius:12px;
    background:#dbeafe; color:#1d4ed8;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
}
.mod-modal-title {
    font-size:17px; font-weight:700; color:#0f172a; margin:0;
}
.mod-modal-subtitle {
    font-size:12px; color:#64748b; margin:2px 0 0; font-weight:500;
}
.mod-close {
    position:absolute; top:16px; right:16px;
    background:none; border:none; cursor:pointer;
    color:#94a3b8; font-size:16px;
    width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s;
}
.mod-close:hover { background:#e2e8f0; color:#334155; }

.mod-modal-body { padding:24px; }
.mod-label {
    display:block; font-size:13px; font-weight:600; color:#334155;
    margin-bottom:10px;
}

.mod-type-cards {
    display:grid; grid-template-columns:1fr 1fr; gap:12px;
    margin-bottom:20px;
}
.mod-type-card { cursor:pointer; }
.mod-type-card input { display:none; }
.mod-type-card-inner {
    border:2px solid #e2e8f0;
    border-radius:12px;
    padding:18px 14px;
    text-align:center;
    transition:all .2s;
    background:#fafbfc;
}
.mod-type-card-inner:hover {
    border-color:#93c5fd;
    background:#f0f9ff;
}
.mod-type-card input:checked ~ .mod-type-card-inner {
    border-color:#3b82f6;
    background:#eff6ff;
    box-shadow:0 0 0 3px rgba(59,130,246,0.15);
}
.mod-type-icon {
    width:44px; height:44px; border-radius:12px;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:18px; margin-bottom:10px;
}
.mod-type-label {
    font-size:14px; font-weight:700; color:#0f172a; margin-bottom:4px;
}
.mod-type-desc {
    font-size:11.5px; color:#64748b; line-height:1.5;
}

.mod-remarks-wrap { margin-top:4px; }
.mod-remarks-wrap textarea {
    resize:vertical; min-height:70px;
    border-radius:10px; font-size:13px;
}

.mod-modal-footer {
    display:flex; justify-content:flex-end; gap:10px;
    padding:16px 24px;
    background:#f8fafc;
    border-top:1px solid #e5e7eb;
}
</style>

<script>
/* ══════════════════════════════════════════════
   Modification Modal Logic (show page)
══════════════════════════════════════════════ */
function openModificationModal(activityId, code) {
    const overlay = document.getElementById('modOverlay');
    const form    = document.getElementById('modForm');
    const subtitle = document.getElementById('modSubtitle');

    form.action = `{{ url('dean_osa/approval') }}/${activityId}/modification`;
    subtitle.textContent = 'SARF Code: ' + code;

    form.reset();
    document.getElementById('modSubmitBtn').disabled = true;
    document.querySelectorAll('.mod-type-card input').forEach(r => r.checked = false);

    overlay.classList.add('active');
}

function closeModificationModal() {
    document.getElementById('modOverlay').classList.remove('active');
}

function selectModType(val) {
    document.getElementById('modSubmitBtn').disabled = false;
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModificationModal();
});
</script>

{{-- ══════════════════════════════════════════════
     RESCHEDULE REVIEW MODAL
══════════════════════════════════════════════ --}}
@if($activity->reschedule_status === 'pending')
<div class="resched-review-overlay" id="reschedReviewOverlay" onclick="closeRescheduleReviewModal()">
    <div class="resched-review-modal" onclick="event.stopPropagation()">

        {{-- Header --}}
        <div class="resched-review-header">
            <div class="resched-review-header-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h3 class="resched-review-title">Review Schedule Change</h3>
                <p class="resched-review-subtitle">{{ $activity->code }} — {{ Str::limit($activity->title, 40) }}</p>
            </div>
            <button type="button" class="resched-review-close" onclick="closeRescheduleReviewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Comparison Cards --}}
        <div class="resched-review-body">
            <div class="resched-compare-grid">
                {{-- Original Schedule --}}
                <div class="resched-compare-card resched-compare-old">
                    <div class="resched-compare-label">
                        <i class="fas fa-history"></i> Original Schedule
                    </div>
                    <div class="resched-compare-fields">
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Date</div>
                            <div class="resched-compare-field-value">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $activity->date_of_activity?->format('M j, Y') ?? '—' }}
                            </div>
                        </div>
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Time</div>
                            <div class="resched-compare-field-value">
                                <i class="fas fa-clock"></i>
                                {{ $activity->time_of_activity ?: '—' }}
                            </div>
                        </div>
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Venue</div>
                            <div class="resched-compare-field-value">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $activity->venue ?: ($activity->platform ?: '—') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Arrow --}}
                <div class="resched-compare-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>

                {{-- Proposed Schedule --}}
                <div class="resched-compare-card resched-compare-new">
                    <div class="resched-compare-label">
                        <i class="fas fa-calendar-check"></i> Proposed Schedule
                    </div>
                    <div class="resched-compare-fields">
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Date</div>
                            <div class="resched-compare-field-value resched-highlight">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $activity->reschedule_date?->format('M j, Y') ?? '—' }}
                            </div>
                        </div>
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Time</div>
                            <div class="resched-compare-field-value {{ filled($activity->reschedule_time) ? 'resched-highlight' : '' }}">
                                <i class="fas fa-clock"></i>
                                {{ $activity->reschedule_time ?: $activity->time_of_activity ?: '—' }}
                            </div>
                        </div>
                        <div class="resched-compare-field">
                            <div class="resched-compare-field-label">Venue</div>
                            <div class="resched-compare-field-value {{ filled($activity->reschedule_venue) ? 'resched-highlight' : '' }}">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $activity->reschedule_venue ?: $activity->venue ?: ($activity->platform ?: '—') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            @if(filled($activity->reschedule_reason))
            <div class="resched-reason-box">
                <div class="resched-reason-label"><i class="fas fa-comment-alt"></i> Reason for Rescheduling</div>
                <div class="resched-reason-text">{{ $activity->reschedule_reason }}</div>
            </div>
            @endif

            {{-- Requested timestamp --}}
            <div style="font-size:11.5px; color:#94a3b8; text-align:right; margin-top:8px;">
                <i class="fas fa-clock" style="font-size:10px;"></i>
                Requested {{ $activity->reschedule_requested_at?->format('M j, Y \a\t g:i A') }}
            </div>

            {{-- Remarks Input --}}
            <div style="margin-top:16px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
                    Your Remarks <span style="color:#94a3b8; font-weight:400;">(optional)</span>
                </label>
                <textarea id="reschedReviewRemarks" class="form-control" rows="2"
                    placeholder="Add remarks about this reschedule request…"
                    style="resize:vertical; font-size:13px; border-radius:10px;"></textarea>
            </div>
        </div>

        {{-- Footer with Actions --}}
        <div class="resched-review-footer">
            <button type="button" class="btn btn-filter" onclick="closeRescheduleReviewModal()">
                <i class="fas fa-arrow-left"></i> Close
            </button>
            <div style="display:flex; gap:8px;">
                <form action="{{ route('dean_osa.approval.reschedule.reject', $activity->id) }}" method="POST" id="reschedRejectForm">
                    @csrf
                    <input type="hidden" name="reschedule_remarks" id="reschedRejectRemarks">
                    <button type="submit" class="btn btn-danger"
                        onclick="document.getElementById('reschedRejectRemarks').value = document.getElementById('reschedReviewRemarks').value;"
                        style="font-size:12.5px;">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
                <form action="{{ route('dean_osa.approval.reschedule.approve', $activity->id) }}" method="POST" id="reschedApproveForm">
                    @csrf
                    <input type="hidden" name="reschedule_remarks" id="reschedApproveRemarks">
                    <button type="submit" class="btn btn-add"
                        onclick="document.getElementById('reschedApproveRemarks').value = document.getElementById('reschedReviewRemarks').value;"
                        style="font-size:12.5px;">
                        <i class="fas fa-check"></i> Approve Schedule
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
