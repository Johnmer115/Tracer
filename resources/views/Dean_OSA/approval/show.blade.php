@extends('Dean_OSA.layouts.layout')

@section('title', 'Review Activity | SARF Tracking')
@section('page-title', 'Review Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/approval-modification-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/approval-show.css') }}">

@endpush

@section('content')
<section class="panel" style="padding: 25px;">

    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @php
        $hasRescheduleRequest = in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature', 'approved'], true)
            && filled($activity->reschedule_requested_at);
        $isRescheduleApproval = $activity->status === 'for approval for rescheduling';
        $hasPendingReschedule = $isRescheduleApproval && in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature'], true);
        $isRescheduleReadyForApproval = $isRescheduleApproval && $activity->reschedule_status === 'for approval';
        $showRescheduleStep = $hasRescheduleRequest;
        $lockNormalStepsForReschedule = $isRescheduleApproval;

        $statusClass = match($activity->status) {
            'pending'              => 'b-pending',
            'ongoing'              => 'b-ongoing',
            'for approval'         => 'b-for-approval',
            'for approval finance' => 'b-for-approval',
            'for approval for rescheduling' => 'b-for-approval',
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
        if ($showRescheduleStep) {
            $pipeline[] = ['label' => 'Rescheduling', 'val' => 'rescheduling'];
        }

        $pipeIdx = collect($pipeline)->search(fn($s) => $s['val'] === $activity->status) ?? -1;
        if ($activity->status === 'for approval finance') $pipeIdx = 2;
        if ($activity->status === 'completed') $pipeIdx = 3;
        if ($showRescheduleStep) $pipeIdx = 4;

        $isApprovalUnlocked  = in_array($activity->status, ['for approval','for approval finance','approved','completed','for approval for rescheduling']);
        $isForFinance        = in_array($activity->status, ['for approval finance','approved','completed','for approval for rescheduling']);
        $isCompleted         = in_array($activity->status, ['approved','completed','for approval for rescheduling']);

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
        $requiresLegalApproval = $activity->waiver_consent === 'With';

        $applicableMainFields = collect($mainFields)
            ->reject(fn($f) => $f === 'approval_dir_basic_ed' && !$requiresBasicEdApproval)
            ->reject(fn($f) => $f === 'approval_vp_hrd_legal' && !$requiresLegalApproval)
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
        $budgetFieldsByApproval = collect(array_merge($signatories, $financeSignatories))
            ->pluck('budget', 'field')
            ->all();
        $approvalBudgetValue = function ($field) use ($activity, $approvalFields, $budgetFieldsByApproval) {
            $ownBudgetField = $budgetFieldsByApproval[$field] ?? null;
            if ($ownBudgetField && $activity->{$ownBudgetField} !== null) {
                return $activity->{$ownBudgetField};
            }

            $idx = array_search($field, $approvalFields, true);
            if ($idx !== false) {
                for ($i = $idx - 1; $i >= 0; $i--) {
                    $previousField = $approvalFields[$i];
                    $previousBudgetField = $budgetFieldsByApproval[$previousField] ?? null;
                    if ($previousBudgetField && $activity->{$previousField} === 'approved' && $activity->{$previousBudgetField} !== null) {
                        return $activity->{$previousBudgetField};
                    }
                }
            }

            return $activity->amount;
        };
        $approvedCount  = collect($approvalFields)->filter(fn($f) => $activity->{$f} === 'approved')->count();
        $totalApprovals = count($approvalFields);
        $progressPct    = $totalApprovals > 0 ? round(($approvedCount / $totalApprovals) * 100) : 0;

        $docs = $activity->sarfDocuments->keyBy('type');
        $approvedSarfDoc = $docs->get('APPROVED_SARF');
        $reschedulePaperDoc = $docs->get('RESCHEDULE_PAPER');
        $approvedDocRemark = old('approved_remark')
            ?? ($approvedSarfDoc ? optional($approvedSarfDoc->remarks->sortByDesc('created_at')->first())->remark : null);

        $requestedTab = (int) request()->query('tab', 0);
        $activeTab = in_array($requestedTab, [1, 2, 3, 4], true)
            ? $requestedTab
            : ($showRescheduleStep && $activity->reschedule_status !== 'approved' ? 4 : ($isCompleted ? 3 : ($isApprovalUnlocked ? 2 : 1)));

        if ($isRescheduleApproval) {
            $activeTab = 4;
        }

        if ($showRescheduleStep && $activity->reschedule_status === 'approved' && $requestedTab === 0) {
            $activeTab = 1;
        }

        $showOriginalScheduleInDetails = $activity->reschedule_status === 'approved';
        $detailsDate = $showOriginalScheduleInDetails ? ($activity->reschedule_original_date ?: $activity->date_of_activity) : $activity->date_of_activity;
        $detailsTime = $showOriginalScheduleInDetails ? ($activity->reschedule_original_time ?: $activity->time_of_activity) : $activity->time_of_activity;
        $detailsMode = $showOriginalScheduleInDetails ? ($activity->reschedule_original_mode ?: $activity->mode_of_conduct) : $activity->mode_of_conduct;
        $detailsVenue = $showOriginalScheduleInDetails ? ($activity->reschedule_original_venue ?: $activity->venue) : $activity->venue;
        $detailsVenueType = $showOriginalScheduleInDetails ? ($activity->reschedule_original_venue_type ?: $activity->venue_type) : $activity->venue_type;
        $detailsPlatform = $showOriginalScheduleInDetails ? ($activity->reschedule_original_platform ?: $activity->platform) : $activity->platform;

        if ($activeTab === 3 && !$isCompleted) {
            $activeTab = $isApprovalUnlocked ? 2 : 1;
        }

        if ($activeTab === 4 && !$showRescheduleStep) {
            $activeTab = $isApprovalUnlocked ? 2 : 1;
        }

        if ($activeTab === 2 && (!$isApprovalUnlocked || $isApprovalFrozen)) {
            $activeTab = 1;
        }
    @endphp

    {{-- ══════════════════════════════════════
         ADVANCE TO APPROVAL POPUP MODAL
    ══════════════════════════════════════ --}}
    <div class="panel">

        {{-- ── Panel Header ── --}}
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-alt"></i>
                {{ Str::limit($activity->title, 48) }}
                <span style="margin-left:8px;">
                    @include('partials.sarf-status-badge', ['activity' => $activity])
                </span>
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
                @if(in_array($activity->status, ['ongoing','for approval','for approval finance','approved']))
                    <div class="workflow-spacer" style="flex:1;"></div>
                    <div class="workflow-side-actions">
                        @if($activity->status === 'approved' && !$hasPendingReschedule)
                            <button type="button" class="btn-quick-action btn-quick-action--mod"
                                onclick="openModificationModal({{ $activity->id }}, '{{ addslashes($activity->code) }}', '{{ $activity->status }}')">
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
                 STEP INDICATORS — blue
            ══════════════════════════════════════ --}}
            <div class="step-indicators">
                <button type="button" id="step-indicator-1"
                    class="step-indicator-btn {{ $isApprovalUnlocked ? 'completed' : '' }} {{ $lockNormalStepsForReschedule ? 'step-locked' : '' }} {{ $activeTab === 1 ? 'active' : '' }}"
                    onclick="{{ $lockNormalStepsForReschedule ? 'return false' : 'showTab(1)' }}"
                    title="{{ $lockNormalStepsForReschedule ? 'Locked because this SARF is already approved. Continue in Reschedule.' : '' }}">
                    @if($lockNormalStepsForReschedule)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-info-circle"></i>
                    @endif
                    1. Event Details
                </button>
                <button type="button" id="step-indicator-2"
                    class="step-indicator-btn {{ $isCompleted ? 'completed' : '' }} {{ (!$isApprovalUnlocked || $isApprovalFrozen || $lockNormalStepsForReschedule) ? 'step-locked' : '' }} {{ $activeTab === 2 ? 'active' : '' }}"
                    onclick="{{ ($isApprovalUnlocked && !$isApprovalFrozen && !$lockNormalStepsForReschedule) ? 'showTab(2)' : 'return false' }}"
                    title="{{ $lockNormalStepsForReschedule ? 'Locked because signatory approvals are already complete.' : ($isApprovalFrozen ? 'Approvals frozen — resolve the pending reschedule first.' : (!$isApprovalUnlocked ? 'Advance to For Approval status first.' : '')) }}">
                    @if($lockNormalStepsForReschedule || $isApprovalFrozen)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @elseif(!$isApprovalUnlocked)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-stamp"></i>
                    @endif
                    2. Approval
                </button>
                <button type="button" id="step-indicator-3"
                    class="step-indicator-btn {{ $isCompleted ? 'completed' : 'step-locked' }} {{ $lockNormalStepsForReschedule ? 'step-locked' : '' }} {{ $activeTab === 3 ? 'active' : '' }}"
                    onclick="{{ ($isCompleted && !$lockNormalStepsForReschedule) ? 'showTab(3)' : 'return false' }}"
                    title="{{ $lockNormalStepsForReschedule ? 'Locked because Approved SARF is already complete.' : (!$isCompleted ? 'Available once all approvals are approved.' : '') }}">
                    @if(!$isCompleted || $lockNormalStepsForReschedule)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-check-double"></i>
                    @endif
                    3. Approved SARF
                </button>
                @if($showRescheduleStep)
                <button type="button" id="step-indicator-4"
                    class="step-indicator-btn step-reschedule {{ $activity->reschedule_status === 'approved' ? 'completed' : '' }} {{ $activeTab === 4 ? 'active' : '' }}"
                    onclick="showTab(4)">
                    @if($hasPendingReschedule)
                        <i class="fas fa-calendar-alt"></i>
                    @else
                        <i class="fas fa-calendar-check"></i>
                    @endif
                    4. Reschedule
                </button>
                @endif
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
                                {{ $detailsDate?->format('F j, Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Time of Activity</div>
                            <div class="show-value">{{ $detailsTime ?? '—' }}</div>
                        </div>

                        <div class="show-field">
                            <div class="show-label">Mode of Conduct</div>
                            <div class="show-value">{{ $detailsMode ?? '—' }}</div>
                        </div>

                        @if(in_array($detailsMode, ['Face to Face','Hybrid']))
                        <div class="show-field">
                            <div class="show-label">Venue</div>
                            <div class="show-value">
                                {{ $detailsVenue ?? '—' }}
                                @if($detailsVenueType)
                                    <span class="inline-tag">{{ $detailsVenueType }}</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(in_array($detailsMode, ['Online','Hybrid']))
                        <div class="show-field">
                            <div class="show-label">Platform</div>
                            <div class="show-value">
                                <i class="fas fa-video" style="color:#3b82f6; font-size:12px;"></i>
                                {{ $detailsPlatform ?? '—' }}
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
                        @php
                            $hasDigitalDocs = $docs->contains(fn ($doc) => filled($doc->file_path));
                        @endphp
                        @if($hasDigitalDocs)
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
                                                @if($docs[$type]->file_path)
                                                    <i class="fas fa-file-pdf" style="color:#ef4444;"></i>
                                                    {{ $docs[$type]->original_filename }}
                                                @else
                                                    <i class="fas fa-file-alt" style="color:#64748b;"></i>
                                                    Hardcopy available
                                                @endif
                                                &nbsp;·&nbsp; {{ $docs[$type]->created_at?->format('M j, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        @if($docs[$type]->file_path)
                                            <a href="{{ route('dean_osa.sarf-documents.show', $docs[$type]) }}"
                                                target="_blank" class="attachment-view-btn">
                                                <i class="fas fa-file-pdf"></i> View PDF
                                            </a>
                                        @else
                                            <span class="attachment-view-btn">
                                                <i class="fas fa-file-alt"></i> Hardcopy available
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Scroll sentinel --}}
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
                            Continue in the Reschedule step.
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
                                                    value="{{ $approvalBudgetValue($sig['field']) }}">
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
                                                    value="{{ $approvalBudgetValue($sig['field']) }}">
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

                {{-- ── Approval Summary (read-only) ── --}}
                @if($isCompleted)
                <div class="show-section" style="margin-top:20px;">
                    <div class="show-section-header amber">
                        <i class="fas fa-clipboard-check"></i> Approval Summary
                    </div>
                    <div style="padding:0; overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:10px 16px; text-align:left; font-weight:700; color:#334155;">Role</th>
                                    <th style="padding:10px 16px; text-align:center; font-weight:700; color:#334155;">Status</th>
                                    <th style="padding:10px 16px; text-align:right; font-weight:700; color:#334155;">Approved Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($signatories as $sig)
                                    @continue(!in_array($sig['field'], $applicableMainFields, true))
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:10px 16px; color:#1e293b; font-weight:500;">{{ $sig['role'] }}</td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <span class="badge approval-status-badge {{ $approvalBadgeClass($activity->{$sig['field']} ?? 'pending') }}">
                                                {{ ucfirst($activity->{$sig['field']} ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; text-align:right; color:#15803d; font-weight:600;">
                                            @if($activity->{$sig['budget']} !== null)
                                                &#8369; {{ number_format($activity->{$sig['budget']}, 2) }}
                                            @else
                                                <span style="color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if($requiresFinanceApproval)
                                    @foreach($financeSignatories as $sig)
                                        @continue(!in_array($sig['field'], $applicableFinanceFields, true))
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td style="padding:10px 16px; color:#1e293b; font-weight:500;">{{ $sig['role'] }}</td>
                                            <td style="padding:10px 16px; text-align:center;">
                                                <span class="badge approval-status-badge {{ $approvalBadgeClass($activity->{$sig['field']} ?? 'pending') }}">
                                                    {{ ucfirst($activity->{$sig['field']} ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td style="padding:10px 16px; text-align:right; color:#15803d; font-weight:600;">
                                                @if($activity->{$sig['budget']} !== null)
                                                    &#8369; {{ number_format($activity->{$sig['budget']}, 2) }}
                                                @else
                                                    <span style="color:#94a3b8;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Final Approved Budget row --}}
                                @php
                                    if ($requiresFinanceApproval) {
                                        $finalBudget = $activity->budget_comptroller_final;
                                    } else {
                                        $lastMainField = collect($signatories)
                                            ->filter(fn($s) => in_array($s['field'], $applicableMainFields, true))
                                            ->last();
                                        $finalBudget = $lastMainField ? $activity->{$lastMainField['budget']} : null;
                                    }
                                @endphp
                                <tr style="background:#f0fdf4; border-top:2px solid #bbf7d0;">
                                    <td colspan="2" style="padding:12px 16px; font-weight:700; color:#15803d; font-size:13.5px;">
                                        <i class="fas fa-coins" style="margin-right:4px;"></i> Final Approved Budget
                                    </td>
                                    <td style="padding:12px 16px; text-align:right; font-weight:700; color:#15803d; font-size:14px;">
                                        @if($finalBudget !== null)
                                            &#8369; {{ number_format($finalBudget, 2) }}
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                        {{ $approvedSarfDoc->original_filename ?? 'Hardcopy available' }}
                                    @else
                                        No approved SARF saved yet
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
                                    <span class="approved-dropzone-sub">PDF upload is optional. Saving without a file marks hardcopy as available.</span>
                                    <span class="approved-file-chip">
                                        <i class="fas fa-file-pdf"></i>
                                        <span id="approved_fname_sarf">
                                            @if($approvedSarfDoc?->file_path)
                                                {{ $approvedSarfDoc->original_filename }}
                                            @elseif($approvedSarfDoc)
                                                Hardcopy available
                                            @else
                                                No file chosen
                                            @endif
                                        </span>
                                    </span>
                                </span>
                            </label>

                            <label for="approved_sarf_hardcopy"
                                style="display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border:1px solid {{ $approvedSarfDoc && !$approvedSarfDoc->file_path ? '#86efac' : '#cbd5e1' }}; border-radius:10px; background:{{ $approvedSarfDoc && !$approvedSarfDoc->file_path ? '#f0fdf4' : '#f8fafc' }}; cursor:pointer;">
                                <input type="checkbox"
                                    id="approved_sarf_hardcopy"
                                    name="approved_sarf_hardcopy"
                                    value="1"
                                    style="width:18px; height:18px; margin-top:2px; accent-color:#16a34a;"
                                    @checked(old('approved_sarf_hardcopy', $approvedSarfDoc && !$approvedSarfDoc->file_path))>
                                <span style="display:flex; flex-direction:column; gap:3px;">
                                    <span style="font-size:13px; font-weight:700; color:#0f172a;">
                                        Approved SARF hardcopy is available
                                    </span>
                                    <span style="font-size:12px; color:#64748b; line-height:1.45;">
                                        Check this when the signed approved SARF exists as a physical document. A PDF upload is still optional.
                                    </span>
                                </span>
                            </label>
                            @error('approved_sarf_hardcopy')
                                <div style="color:#b91c1c; font-size:12px;">{{ $message }}</div>
                            @enderror

                            <div class="document-check-row">
                                <a href="#"
                                    target="_blank"
                                    class="document-check-btn document-preview-btn"
                                    id="preview_btn_sarf">
                                    <i class="fas fa-eye"></i> Preview Selected File
                                </a>
                                @if($approvedSarfDoc?->file_path)
                                    <a href="{{ route('dean_osa.sarf-documents.show', $approvedSarfDoc) }}"
                                        target="_blank" class="document-check-btn">
                                        <i class="fas fa-file-pdf"></i> View Document
                                    </a>
                                    <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $approvedSarfDoc, 'download' => 1]) }}"
                                        class="document-check-btn document-download-btn">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                @elseif($approvedSarfDoc)
                                    <span class="document-check-btn">
                                        <i class="fas fa-file-alt"></i> Hardcopy available
                                    </span>
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

            {{-- TAB 4 - RESCHEDULE --}}
            <div id="tab-4" style="{{ $activeTab === 4 ? '' : 'display:none;' }}">

                @if(!$isCompleted)
                    <div class="notice-card notice-card--blue">
                        <i class="fas fa-lock"></i>
                        Rescheduling is available after the SARF is fully approved.
                    </div>
                @elseif(!$showRescheduleStep)
                    <div class="notice-card notice-card--blue">
                        <i class="fas fa-calendar-check"></i>
                        No pending reschedule request for this activity.
                    </div>
                @else
                    @if($activity->reschedule_status === 'approved' && $reschedulePaperDoc)
                        <div class="notice-card notice-card--success">
                            <i class="fas fa-calendar-check"></i>
                            <strong>Approved Reschedule.</strong>
                            The updated schedule is now reflected in the event details below.
                        </div>

                        <div class="show-section">
                            <div class="show-section-header green">
                                <i class="fas fa-clock"></i> Updated Schedule, Conduct & Extras
                            </div>
                            <div class="show-grid">
                                <div class="show-field">
                                    <div class="show-label">Date of Activity</div>
                                    <div class="show-value">{{ $activity->date_of_activity?->format('F j, Y') ?? '---' }}</div>
                                </div>
                                <div class="show-field">
                                    <div class="show-label">Time of Activity</div>
                                    <div class="show-value">{{ $activity->time_of_activity ?? '---' }}</div>
                                </div>
                                <div class="show-field">
                                    <div class="show-label">Mode of Conduct</div>
                                    <div class="show-value">{{ $activity->mode_of_conduct ?? '---' }}</div>
                                </div>
                                @if(in_array($activity->mode_of_conduct, ['Face to Face','Hybrid']))
                                    <div class="show-field">
                                        <div class="show-label">Venue</div>
                                        <div class="show-value">
                                            {{ $activity->venue ?? '---' }}
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
                                            {{ $activity->platform ?? '---' }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="signatory-card" style="margin-top:16px;">
                            <div class="signatory-header">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="fas fa-calendar-check" style="color:#16a34a; font-size:12px;"></i>
                                    <span style="font-weight:600; font-size:13.5px; color:#1e293b;">Reschedule Approval</span>
                                </div>
                                <span class="badge approval-status-badge b-approved">Approved</span>
                            </div>
                            <div class="signatory-body" style="display:block;">
                                <div class="notice-card notice-card--success" style="margin:0;">
                                    <i class="fas fa-check-circle"></i>
                                    <div>
                                        <strong>Reschedule approved.</strong>
                                        Approved {{ $activity->reschedule_decided_at?->format('M j, Y \a\t g:i A') ?? '---' }}.
                                        @if(filled($activity->reschedule_remarks))
                                            <div style="margin-top:4px;">Remarks: {{ $activity->reschedule_remarks }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="show-section" style="margin-top:16px;">
                            <div class="show-section-header">
                                <i class="fas fa-file-pdf"></i> Reschedule Document
                            </div>
                            @if($reschedulePaperDoc)
                                <div style="padding:16px 16px 0; font-size:12.5px; color:#334155; display:flex; align-items:center; gap:8px;">
                                    <i class="fas fa-file-pdf" style="color:#dc2626;"></i>
                                    <span style="font-weight:600;">{{ $reschedulePaperDoc->original_filename }}</span>
                                </div>
                                <div class="document-check-row" style="padding:16px;">
                                    <a href="{{ route('dean_osa.sarf-documents.show', $reschedulePaperDoc) }}"
                                        target="_blank" class="document-check-btn">
                                        <i class="fas fa-file-pdf"></i> View Reschedule Paper
                                    </a>
                                    <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $reschedulePaperDoc, 'download' => 1]) }}"
                                        class="document-check-btn document-download-btn">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            @else
                                <div style="padding:16px; color:#94a3b8; font-size:13px;">No reschedule document uploaded.</div>
                            @endif
                        </div>
                    @else
                        <div class="notice-card notice-card--blue">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Reschedule Review.</strong>
                            Review the original and proposed schedule before updating the approval status.
                        </div>

                        <div class="resched-review-body" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff;">
                            <div class="resched-compare-grid">
                                <div class="resched-compare-card resched-compare-old">
                                    <div class="resched-compare-label">
                                        <i class="fas fa-history"></i> Original Schedule
                                    </div>
                                    <div class="resched-compare-fields">
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Date</div>
                                            <div class="resched-compare-field-value">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ ($activity->reschedule_original_date ?: $activity->date_of_activity)?->format('M j, Y') ?? '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Time</div>
                                            <div class="resched-compare-field-value">
                                                <i class="fas fa-clock"></i>
                                                {{ $activity->reschedule_original_time ?: $activity->time_of_activity ?: '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Mode</div>
                                            <div class="resched-compare-field-value">
                                                <i class="fas fa-users"></i>
                                                {{ $activity->reschedule_original_mode ?: $activity->mode_of_conduct ?: '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Venue</div>
                                            <div class="resched-compare-field-value">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $activity->reschedule_original_venue ?: ($activity->reschedule_original_platform ?: ($activity->venue ?: ($activity->platform ?: '---'))) }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Venue Type</div>
                                            <div class="resched-compare-field-value">
                                                <i class="fas fa-location-dot"></i>
                                                {{ $activity->reschedule_original_venue_type ?: $activity->venue_type ?: '---' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="resched-compare-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>

                                <div class="resched-compare-card resched-compare-new">
                                    <div class="resched-compare-label">
                                        <i class="fas fa-calendar-check"></i> Proposed Schedule
                                    </div>
                                    <div class="resched-compare-fields">
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Date</div>
                                            <div class="resched-compare-field-value resched-highlight">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ $activity->reschedule_date?->format('M j, Y') ?? '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Time</div>
                                            <div class="resched-compare-field-value {{ filled($activity->reschedule_time) ? 'resched-highlight' : '' }}">
                                                <i class="fas fa-clock"></i>
                                                {{ $activity->reschedule_time ?: '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Mode</div>
                                            <div class="resched-compare-field-value {{ filled($activity->reschedule_mode) ? 'resched-highlight' : '' }}">
                                                <i class="fas fa-users"></i>
                                                {{ $activity->reschedule_mode ?: '---' }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Venue</div>
                                            <div class="resched-compare-field-value {{ filled($activity->reschedule_venue) ? 'resched-highlight' : '' }}">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $activity->reschedule_venue ?: ($activity->reschedule_platform ?: '---') }}
                                            </div>
                                        </div>
                                        <div class="resched-compare-field">
                                            <div class="resched-compare-field-label">Venue Type</div>
                                            <div class="resched-compare-field-value {{ filled($activity->reschedule_venue_type) ? 'resched-highlight' : '' }}">
                                                <i class="fas fa-location-dot"></i>
                                                {{ $activity->reschedule_venue_type ?: '---' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(filled($activity->reschedule_reason))
                                <div class="resched-reason-box">
                                    <div class="resched-reason-label"><i class="fas fa-comment-alt"></i> Reason for Rescheduling</div>
                                    <div class="resched-reason-text">{{ $activity->reschedule_reason }}</div>
                                </div>
                            @endif

                            <div style="font-size:11.5px; color:#94a3b8; text-align:right; margin-top:8px;">
                                <i class="fas fa-clock" style="font-size:10px;"></i>
                                Requested {{ $activity->reschedule_requested_at?->format('M j, Y \a\t g:i A') }}
                            </div>
                        </div>

                        @if($isRescheduleApproval || $activity->reschedule_status === 'approved')
                            @php
                                $reschedDocUnlocked = $activity->reschedule_status === 'approved';
                                $rescheduleDocumentLocked = !$reschedDocUnlocked;
                                $stepTwoCircleBg = $reschedulePaperDoc ? '#16a34a' : ($rescheduleDocumentLocked ? '#94a3b8' : '#3b82f6');
                            @endphp

                            {{-- Step 1: Approval --}}
                            <div style="margin-top:16px;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                                    <div style="width:24px; height:24px; border-radius:50%; background:{{ $reschedDocUnlocked ? '#16a34a' : '#3b82f6' }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700;">
                                        @if($reschedDocUnlocked)<i class="fas fa-check"></i>@else 1 @endif
                                    </div>
                                    <span style="font-weight:700; font-size:13px; color:#1e293b;">Step 1: Status & Approval</span>
                                    @if($reschedDocUnlocked)
                                        <span class="mini-pill pill-green" style="margin-left:auto;">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                    @endif
                                </div>

                                @if(false)
                                    {{-- Already approved — show read-only summary --}}
                                    <div class="signatory-card">
                                        <div class="signatory-header">
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <i class="fas fa-calendar-check" style="color:#16a34a; font-size:12px;"></i>
                                                <span style="font-weight:600; font-size:13.5px; color:#1e293b;">Reschedule Application Approval</span>
                                            </div>
                                            <span class="badge approval-status-badge b-approved">Approved</span>
                                        </div>
                                        <div class="signatory-body" style="display:block;">
                                            <div class="notice-card notice-card--success" style="margin:0;">
                                                <i class="fas fa-check-circle"></i>
                                                <div>
                                                    <strong>Reschedule approved.</strong>
                                                    Approved {{ $activity->reschedule_decided_at?->format('M j, Y \a\t g:i A') ?? '---' }}.
                                                    @if(filled($activity->reschedule_remarks))
                                                        <div style="margin-top:4px;">Remarks: {{ $activity->reschedule_remarks }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                <form action="{{ route('dean_osa.approval.reschedule.approve', $activity->id) }}"
                                    method="POST"
                                    id="reschedApprovalForm">
                                    @csrf
                                    <input type="hidden" name="save_action" value="approval">

                                    <div class="signatory-card">
                                        <div class="signatory-header">
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <i class="fas fa-calendar-check" style="color:#d97706; font-size:12px;"></i>
                                                <span style="font-weight:600; font-size:13.5px; color:#1e293b;">Reschedule Application Approval</span>
                                            </div>
                                            <span class="badge approval-status-badge {{ in_array($activity->reschedule_status, ['for approval', 'for signature'], true) ? 'b-for-signature' : ($activity->reschedule_status === 'approved' ? 'b-approved' : 'b-pending') }}">
                                                @if($activity->reschedule_status === 'for signature')
                                                    For Signature
                                                @elseif($activity->reschedule_status === 'for approval')
                                                    For Approval
                                                @elseif($activity->reschedule_status === 'approved')
                                                    Approved
                                                @else
                                                    Pending Review
                                                @endif
                                            </span>
                                        </div>

                                        <div class="signatory-body">
                                            <div style="display:grid; grid-template-columns:minmax(170px,220px) minmax(0,1fr); gap:16px; align-items:start;">
                                                <div>
                                                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">Status</label>
                                                    <select name="reschedule_status" class="filter-select" style="width:100%;">
                                                        <option value="pending" @selected($activity->reschedule_status === 'pending')>Pending</option>
                                                        <option value="for signature" @selected($activity->reschedule_status === 'for signature')>For Signature</option>
                                                        <option value="approved" @selected($activity->reschedule_status === 'approved')>Approved</option>
                                                        <option value="disapproved" @selected($activity->reschedule_status === 'disapproved')>Disapproved</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
                                                        Approval Remarks <span style="color:#94a3b8; font-weight:400;">(optional)</span>
                                                    </label>
                                                    <textarea name="reschedule_remarks" class="form-control" rows="3"
                                                        placeholder="Add remarks about this reschedule request..."
                                                        style="resize:vertical; font-size:13px; border-radius:10px;">{{ $activity->reschedule_remarks }}</textarea>
                                                </div>
                                            </div>
                                            <div style="display:flex; justify-content:flex-end; margin-top:14px;">
                                                <button type="submit" class="btn btn-add" style="font-size:12.5px;">
                                                    <i class="fas fa-save"></i> Save Approval
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                @endif
                            </div>

                            {{-- Step 2: Document --}}
                            <div style="margin-top:20px;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                                    <div style="width:24px; height:24px; border-radius:50%; background:{{ $stepTwoCircleBg }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700;">
                                        @if($reschedulePaperDoc)<i class="fas fa-check"></i>@else 2 @endif
                                    </div>
                                    <span style="font-weight:700; font-size:13px; color:#1e293b;">Step 2: Reschedule Document Upload</span>
                                    @if($rescheduleDocumentLocked)
                                        <span style="margin-left:auto; font-size:11px; color:#94a3b8; font-weight:600;">
                                            <i class="fas fa-lock"></i> Locked
                                        </span>
                                    @elseif($reschedulePaperDoc)
                                        <span class="mini-pill pill-green" style="margin-left:auto;">
                                            <i class="fas fa-check"></i> Uploaded
                                        </span>
                                    @endif
                                </div>

                                <div class="show-section" id="reschedule-document-section" style="{{ $rescheduleDocumentLocked ? 'opacity:0.58;' : '' }}">
                                    <div class="show-section-header">
                                        <i class="fas fa-file-pdf"></i> Reschedule Document
                                    </div>
                                    <div style="padding:16px;">
                                        @if($rescheduleDocumentLocked)
                                            <div class="notice-card notice-card--blue" style="margin:0 0 12px;">
                                                <i class="fas fa-lock"></i>
                                                <div>Save Step 1 status as <strong>Approved</strong> first to unlock document upload.</div>
                                            </div>
                                        @endif
                                            <form action="{{ route('dean_osa.approval.reschedule.approve', $activity->id) }}"
                                                method="POST"
                                                enctype="multipart/form-data"
                                                id="reschedDocForm">
                                                @csrf
                                                <input type="hidden" name="save_action" value="document">

                                                <label class="approved-dropzone is-visible" for="reschedule_paper_file" style="min-height:150px; {{ $rescheduleDocumentLocked ? 'cursor:not-allowed;' : '' }}">
                                                    <input type="file" name="reschedule_paper_file"
                                                        id="reschedule_paper_file" accept=".pdf"
                                                        onchange="updateApprovedFileName('reschedule', this)"
                                                        @disabled($rescheduleDocumentLocked)>
                                                    <span class="approved-dropzone-inner">
                                                        <i class="fas {{ $rescheduleDocumentLocked ? 'fa-lock' : 'fa-cloud-upload-alt' }}"></i>
                                                        <span class="approved-dropzone-main">Upload reschedule paper</span>
                                                        <span class="approved-dropzone-sub">
                                                            {{ $rescheduleDocumentLocked ? 'Locked until Step 1 is approved' : ($reschedulePaperDoc ? 'Replace the current PDF, up to 10MB' : 'PDF format, up to 10MB') }}
                                                        </span>
                                                        <span class="approved-file-chip">
                                                            <i class="fas fa-file-pdf"></i>
                                                            <span id="approved_fname_reschedule">
                                                                {{ $reschedulePaperDoc?->original_filename ?? 'No file chosen' }}
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                                <a href="#"
                                                    target="_blank"
                                                    class="document-check-btn document-preview-btn"
                                                    id="preview_btn_reschedule">
                                                    <i class="fas fa-eye"></i> Preview Selected File
                                                </a>
                                                @error('reschedule_paper_file')
                                                    <div style="margin-top:8px; color:#b91c1c; font-size:12px;">{{ $message }}</div>
                                                @enderror
                                                <div style="display:flex; justify-content:flex-end; margin-top:14px;">
                                                    <button type="submit" class="btn btn-add" style="font-size:12.5px;" @disabled($rescheduleDocumentLocked)>
                                                        <i class="fas fa-save"></i> Save Document
                                                    </button>
                                                </div>
                                            </form>

                                        @if($reschedulePaperDoc)
                                            <div class="document-check-row" style="margin-top:14px;">
                                                <a href="{{ route('dean_osa.sarf-documents.show', $reschedulePaperDoc) }}"
                                                    target="_blank" class="document-check-btn">
                                                    <i class="fas fa-file-pdf"></i> View Current Paper
                                                </a>
                                                <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $reschedulePaperDoc, 'download' => 1]) }}"
                                                    class="document-check-btn document-download-btn">
                                                    <i class="fas fa-download"></i> Download File
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="signatory-card" style="margin-top:16px;">
                                <div class="signatory-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fas fa-calendar-check" style="color:#d97706; font-size:12px;"></i>
                                        <span style="font-weight:600; font-size:13.5px; color:#1e293b;">Reschedule Application Approval</span>
                                    </div>
                                    <span class="badge approval-status-badge b-pending">Pending Review</span>
                                </div>
                                <div class="signatory-body" style="display:block;">
                                    <div class="notice-card notice-card--blue" style="margin:0;">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <strong>Pending review.</strong>
                                            Opened from the approval list, this request will move to For Approval.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

            </div>{{-- /tab-4 --}}

        </div>{{-- /padding --}}
    </div>{{-- /panel --}}
</section>

<script>
/* ── Tab navigation ── */
const TOTAL_TABS = 4;
const INITIAL_TAB = @json($activeTab);
const SHOW_RESCHEDULE_STEP = @json($showRescheduleStep);

function showTab(n, options = {}) {
    if (n === 4 && !SHOW_RESCHEDULE_STEP) return;
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

    if ([1, 2, 3].includes(tabFromUrl) || (tabFromUrl === 4 && SHOW_RESCHEDULE_STEP)) {
        showTab(tabFromUrl, { scroll: !focusTarget });
    } else {
        showTab(INITIAL_TAB, { scroll: false });
    }

    scrollToFocusTarget(focusTarget);
    setupApprovedDropzones();
});

/* ── Scroll-triggered advance popup ── */
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

                    <label class="mod-type-card" id="modCardRescheduling">
                        <input type="radio" name="modification_type" value="rescheduling" required
                            onchange="selectModType(this.value)">
                        <div class="mod-type-card-inner">
                            <div class="mod-type-icon" style="background:#fef3c7; color:#92400e;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="mod-type-label">Rescheduling</div>
                            <div class="mod-type-desc">
                                Change schedule details.<br>
                                Only approved activities can be rescheduled.
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


<script>
/* ══════════════════════════════════════════════
   Modification Modal Logic (show page)
══════════════════════════════════════════════ */
function openModificationModal(activityId, code, status) {
    const overlay = document.getElementById('modOverlay');
    const form    = document.getElementById('modForm');
    const subtitle = document.getElementById('modSubtitle');
    const rescheduleCard = document.getElementById('modCardRescheduling');
    const rescheduleInput = rescheduleCard?.querySelector('input');
    const canReschedule = status === 'approved';

    form.action = `{{ url('dean_osa/approval') }}/${activityId}/modification`;
    subtitle.textContent = 'SARF Code: ' + code;

    form.reset();
    document.getElementById('modSubmitBtn').disabled = true;
    document.querySelectorAll('.mod-type-card input').forEach(r => r.checked = false);
    rescheduleCard?.classList.toggle('is-disabled', !canReschedule);
    if (rescheduleInput) {
        rescheduleInput.disabled = !canReschedule;
    }

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

@endsection
