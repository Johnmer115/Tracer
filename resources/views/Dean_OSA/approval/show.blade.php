@extends('Dean_OSA.layouts.layout')

@section('title', 'Review Activity | SARF Tracking')
@section('page-title', 'Review Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <style>
    /* ══════════════════════════════════════════════
       Show-section cards (mirrors activity/show.blade)
    ══════════════════════════════════════════════ */
    .show-section {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .show-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 20px;
        font-size: 13.5px;
        font-weight: 700;
        color: #1e293b;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }
    .show-section-header i { color: #3b82f6; font-size: 14px; }
    .show-section-header.purple { background: #faf5ff; border-bottom-color: #e9d5ff; }
    .show-section-header.purple i { color: #8b5cf6; }
    .show-section-header.green  { background: #f0fdf4; border-bottom-color: #bbf7d0; }
    .show-section-header.green  i { color: #16a34a; }
    .show-section-header.amber  { background: #fffbeb; border-bottom-color: #fde68a; }
    .show-section-header.amber  i { color: #d97706; }

    .show-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .show-field {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .show-field:nth-child(odd)  { border-right: 1px solid #f1f5f9; }
    .show-field.full            { grid-column: 1 / -1; border-right: none; }
    .show-field:last-child,
    .show-field:nth-last-child(2):nth-child(odd) { border-bottom: none; }
    .show-field.full:last-child { border-bottom: none; }
    .show-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 5px;
    }
    .show-value {
        font-size: 13.5px;
        color: #1e293b;
        line-height: 1.5;
        font-weight: 500;
    }
    .show-value.muted { color: #94a3b8; font-style: italic; }

    /* Tags (levels, depts) */
    .tag-display { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 2px; }
    .tag-display .tag {
        background: #dbeafe; color: #1d4ed8;
        border-radius: 20px; padding: 3px 10px;
        font-size: 12.5px; font-weight: 600; cursor: default;
    }
    .tag-display .tag.purple { background: #ede9fe; color: #6d28d9; }
    .tag-display .tag.green  { background: #dcfce7; color: #15803d; }
    .tag-display .tag.slate  { background: #f1f5f9; color: #475569; }

    /* Objectives */
    .obj-display {
        list-style: none; margin: 4px 0 0; padding: 0;
        display: flex; flex-direction: column; gap: 6px;
    }
    .obj-display li {
        display: flex; align-items: flex-start; gap: 8px;
        font-size: 13.5px; color: #1e293b;
    }
    .obj-display li::before {
        content: ''; flex-shrink: 0;
        width: 7px; height: 7px; border-radius: 50%;
        background: #3b82f6; margin-top: 6px;
    }

    /* Inline badge (venue type etc.) */
    .inline-tag {
        display: inline-block; font-size: 11px; font-weight: 700;
        background: #e2e8f0; color: #475569;
        border-radius: 5px; padding: 2px 8px;
        margin-left: 8px; vertical-align: middle;
    }

    /* Amount highlights */
    .amount-green { font-weight: 700; color: #16a34a; font-size: 15px; }
    .amount-amber { font-weight: 700; color: #d97706; font-size: 15px; }

    /* Late submission */
    .late-notice {
        display: flex; align-items: flex-start; gap: 12px;
        background: #fef9c3; border: 1px solid #fde68a;
        border-radius: 10px; padding: 14px 18px;
        margin-bottom: 16px; font-size: 13.5px; color: #78350f;
    }
    .late-notice i { color: #f59e0b; font-size: 18px; margin-top: 1px; }

    /* Attachment rows */
    .attachment-view-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; padding: 12px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .12s;
    }
    .attachment-view-row:last-child { border-bottom: none; }
    .attachment-view-row:hover { background: #f8fafc; }
    .attachment-view-left {
        display: flex; align-items: center; gap: 10px;
        font-size: 13.5px; color: #1e293b;
    }
    .attachment-view-btn {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #3b82f6;
        background: #dbeafe; border-radius: 20px; padding: 4px 12px;
        text-decoration: none; white-space: nowrap; transition: background .15s;
    }
    .attachment-view-btn:hover { background: #bfdbfe; color: #1d4ed8; }

    /* ══════════════════════════════════════════════
       Step indicators — BLUE theme
    ══════════════════════════════════════════════ */
    .step-indicators {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 28px;
    }
    .step-indicator-btn {
        flex: 1;
        min-width: 120px;
        padding: 10px 8px;
        border: 0;
        border-radius: 8px;
        background: #f1f5f9;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        font-family: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all .2s;
    }
    .step-indicator-btn:hover:not(.step-locked) {
        background: #e2e8f0;
        color: #475569;
    }
    .step-indicator-btn.active {
        background: #3b82f6;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(59,130,246,.16);
    }
    .step-indicator-btn.step-locked {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .step-indicator-btn.completed {
        background: #dcfce7;
        color: #16a34a;
    }
    .step-indicator-btn.completed.active {
        background: #3b82f6;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }

    .btn-quick-action {
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid transparent;
        transition: all .15s ease;
        text-decoration: none;
    }
    .btn-quick-action:active { transform: translateY(1px); }

    .btn-quick-action--danger {
        background: #fff1f2;
        color: #be123c;
        border-color: #fda4af;
    }
    .btn-quick-action--danger:hover {
        background: #ffe4e6;
        border-color: #fb7185;
        color: #9f1239;
    }

    .btn-quick-action--blue {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    .btn-quick-action--blue:hover {
        background: #dbeafe;
        border-color: #60a5fa;
        color: #1e40af;
    }

    /* ══════════════════════════════════════════════
       Signatory cards
    ══════════════════════════════════════════════ */
    .signatory-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        transition: opacity .2s;
    }
    .signatory-card--locked { opacity: 0.55; }
    .signatory-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .signatory-remark {
        padding: 8px 16px; font-size: 12px;
        background: #fffbeb; border-bottom: 1px solid #fde68a; color: #78350f;
        display: flex; gap: 8px; align-items: flex-start;
    }
    .signatory-body { padding: 13px 16px; }

    /* Notice banners */
    .notice-card {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px 16px; border-radius: 8px;
        font-size: 13px; font-weight: 500; margin-bottom: 16px;
    }
    .notice-card--warn    { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .notice-card--success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; }
    .notice-card--blue    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }

    /* Sarf badge */
    .sarf-badge {
        background: #e2e8f0; color: #334155; border-radius: 6px;
        padding: 4px 10px; font-size: 13px; font-weight: 700;
        flex-shrink: 0; display: inline-block;
    }

    /* Shared pill helpers */
    .mini-pill {
        display: inline-block; font-size: 11px; font-weight: 600;
        border-radius: 20px; padding: 2px 8px; white-space: nowrap;
    }
    .pill-blue  { background: #dbeafe; color: #1d4ed8; }
    .pill-slate { background: #f1f5f9; color: #475569; }
    .pill-green { background: #dcfce7; color: #15803d; }
    .pill-amber { background: #fef9c3; color: #92400e; }

    .td-main { font-size: 13.5px; font-weight: 600; color: #1e293b; }
    .td-sub  { font-size: 11.5px; color: #94a3b8; margin-top: 2px; line-height: 1.4; }
    .td-muted { color: #94a3b8; }

    @media (max-width: 640px) {
        .show-grid { grid-template-columns: 1fr; }
        .show-field.full,
        .show-field:nth-child(odd) { border-right: none; }
        .step-indicators { flex-direction: column; }
    }
    </style>
@endpush

@section('content')
<section class="panel" style="padding: 25px;">

    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @php
        $statusClass = match($activity->status) {
            'pending'              => 'b-pending',
            'ongoing'              => 'b-ongoing',
            'for approval'         => 'b-for-approval',
            'for approval finance' => 'b-for-approval',
            'completed'            => 'b-completed',
            'for revision'         => 'b-revision',
            'cancelled'            => 'b-cancelled',
            default                => 'b-pending',
        };

        $pipeline = [
            ['label' => 'Pending',      'val' => 'pending'],
            ['label' => 'Ongoing',      'val' => 'ongoing'],
            ['label' => 'For Approval', 'val' => 'for approval'],
            ['label' => 'Completed',    'val' => 'completed'],
        ];
        $pipeIdx = collect($pipeline)->search(fn($s) => $s['val'] === $activity->status) ?? -1;
        if ($activity->status === 'for approval finance') $pipeIdx = 2;

        $isApprovalUnlocked  = in_array($activity->status, ['for approval','for approval finance','completed']);
        $isForFinance        = in_array($activity->status, ['for approval finance','completed']);
        $isCompleted         = $activity->status === 'completed';
        $showAdvancePopup    = in_array($activity->status, ['pending','ongoing']);

        $signatories = [
            ['field' => 'approval_dean_sa',      'remark' => 'remarks_dean_sa',      'role' => 'Dean for Student Affairs'],
            ['field' => 'approval_avp_sps',      'remark' => 'remarks_avp_sps',      'role' => 'Assistant Vice President for Student Personnel Services'],
            ['field' => 'approval_dir_basic_ed', 'remark' => 'remarks_dir_basic_ed', 'role' => 'Director for Basic Education'],
            ['field' => 'approval_vp_acad',      'remark' => 'remarks_vp_acad',      'role' => 'Vice President for Academic Affairs'],
            ['field' => 'approval_vp_hrd_legal', 'remark' => 'remarks_vp_hrd_legal', 'role' => 'Vice President for HRD/Legal Director Division'],
        ];
        $financeSignatories = [
            ['field' => 'approval_vp_comptroller',  'remark' => 'remarks_vp_comptroller',  'role' => 'Vice President / Comptroller'],
            ['field' => 'approval_avp_finance',     'remark' => 'remarks_avp_finance',     'role' => 'Assistant VP for Finance / Vice President'],
        ];

        $mainFields    = array_column($signatories, 'field');
        $financeFields = array_column($financeSignatories, 'field');

        $mainLocked = [];
        foreach ($mainFields as $i => $f) {
            $mainLocked[$f] = ($i > 0) && ($activity->{$mainFields[$i-1]} !== 'approved');
        }
        $financeLocked = [];
        foreach ($financeFields as $i => $f) {
            $financeLocked[$f] = ($i > 0) && ($activity->{$financeFields[$i-1]} !== 'approved');
        }

        $approvalBadgeClass = fn($v) => match($v ?? 'pending') {
            'approved'    => 'b-approved',
            'disapproved' => 'b-rejected',
            'for signature' => 'b-for-approval',
            default       => 'b-pending',
        };

        $allMainApproved    = collect($mainFields)->every(fn($f) => $activity->{$f} === 'approved');
        $allFinanceApproved = collect($financeFields)->every(fn($f) => $activity->{$f} === 'approved');

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

        $levels     = is_array($activity->level)     ? $activity->level     : (filled($activity->level)     ? [$activity->level]     : []);
        $depts      = is_array($activity->department) ? $activity->department : (filled($activity->department) ? [$activity->department] : []);
        $objectives = is_array($activity->objectives) ? $activity->objectives : (filled($activity->objectives) ? [$activity->objectives] : []);

        $approvalFields = array_merge($mainFields, $financeFields);
        $approvedCount  = collect($approvalFields)->filter(fn($f) => $activity->{$f} === 'approved')->count();
        $totalApprovals = count($approvalFields);
        $progressPct    = $totalApprovals > 0 ? round(($approvedCount / $totalApprovals) * 100) : 0;

        $docs = $activity->sarfDocuments->keyBy('type');
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
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="for approval">
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
            @if(!in_array($activity->status, ['completed','cancelled']))
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; align-items:center;">
                @if($activity->status === 'ongoing')
                    <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="for approval">
                        <button type="submit" class="btn-quick-action btn-quick-action--blue">
                            <i class="fas fa-stamp"></i> Advance to For Approval
                        </button>
                    </form>
                @endif
                @if($activity->status === 'for revision')
                    <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="ongoing">
                        <button class="btn btn-add">
                            <i class="fas fa-redo"></i> Reopen as Ongoing
                        </button>
                    </form>
                @endif
                @if(in_array($activity->status, ['ongoing','for approval','for approval finance']))
                    <div style="flex:1;"></div>
                    <form action="{{ route('dean_osa.approval.status', $activity->id) }}" method="POST"
                        onsubmit="return confirm('Cancel this activity?');">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn-quick-action btn-quick-action--danger">
                            <i class="fas fa-times"></i> Cancel Activity
                        </button>
                    </form>
                @endif
            </div>
            @endif

            {{-- ══════════════════════════════════════
                 STEP INDICATORS — blue
            ══════════════════════════════════════ --}}
            <div class="step-indicators">
                <button type="button" id="step-indicator-1"
                    class="step-indicator-btn {{ $isApprovalUnlocked ? 'completed' : '' }} active"
                    onclick="showTab(1)">
                    <i class="fas fa-info-circle"></i> 1. Event Details
                </button>
                <button type="button" id="step-indicator-2"
                    class="step-indicator-btn {{ $isCompleted ? 'completed' : '' }} {{ !$isApprovalUnlocked ? 'step-locked' : '' }}"
                    onclick="{{ $isApprovalUnlocked ? 'showTab(2)' : 'return false' }}"
                    title="{{ !$isApprovalUnlocked ? 'Advance to For Approval status first.' : '' }}">
                    @if(!$isApprovalUnlocked)
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                    @else
                        <i class="fas fa-stamp"></i>
                    @endif
                    2. Approval
                </button>
                <button type="button" id="step-indicator-3"
                    class="step-indicator-btn {{ !$isCompleted ? 'step-locked' : '' }}"
                    onclick="{{ $isCompleted ? 'showTab(3)' : 'return false' }}"
                    title="{{ !$isCompleted ? 'Available once all approvals are completed.' : '' }}">
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
            <div id="tab-1">

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
                            <div class="show-label">Department / Organization(s)</div>
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

                        <div class="show-field">
                            <div class="show-label">Public Poster</div>
                            <div class="show-value">{{ $activity->public_poster ?? '—' }}</div>
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

                        @if($activity->funds === 'ATC')
                        <div class="show-field">
                            <div class="show-label">Amount</div>
                            <div class="show-value amount-green">
                                {{ $activity->amount ? '₱ ' . number_format($activity->amount, 2) : '—' }}
                            </div>
                        </div>
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
                                    <a href="{{ asset('storage/' . $docs[$type]->file_path) }}"
                                        target="_blank" class="attachment-view-btn">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
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
                    @if($isApprovalUnlocked)
                        <button type="button" onclick="showTab(2)" class="btn btn-add">
                            Approval <i class="fas fa-arrow-right"></i>
                        </button>
                    @endif
                </div>

            </div>{{-- /tab-1 --}}


            {{-- ══════════════════════════════════════
                 TAB 2 — APPROVAL
            ══════════════════════════════════════ --}}
            <div id="tab-2" style="display:none;">

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
                                $locked  = $mainLocked[$sig['field']] || !$isApprovalUnlocked;
                                $current = $activity->{$sig['field']} ?? 'pending';
                            @endphp
                            <div class="signatory-card {{ $locked ? 'signatory-card--locked' : '' }}">
                                <div class="signatory-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fas {{ $locked ? 'fa-lock' : 'fa-user-tie' }}"
                                            style="color:{{ $locked ? '#94a3b8' : '#3b82f6' }}; font-size:12px;"></i>
                                        <span style="font-weight:600; font-size:13.5px; color:#1e293b;">
                                            {{ $sig['role'] }}
                                        </span>
                                    </div>
                                    <span class="badge {{ $approvalBadgeClass($current) }}">
                                        {{ ucfirst($current) }}
                                    </span>
                                </div>
                                @if($activity->{$sig['remark']})
                                    <div class="signatory-remark">
                                        <i class="fas fa-comment-dots" style="color:#d97706;"></i>
                                        {{ $activity->{$sig['remark']} }}
                                    </div>
                                @endif
                                <div class="signatory-body">
                                    @if($locked)
                                        <span class="td-muted" style="font-size:12px;">
                                            <i class="fas fa-lock"></i>
                                            {{ !$isApprovalUnlocked
                                                ? 'Unlock by advancing status to For Approval.'
                                                : 'Waiting for previous signatory.' }}
                                        </span>
                                    @else
                                        <form action="{{ route('dean_osa.approval.approve', $activity->id) }}"
                                            method="POST"
                                            style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="approver" value="{{ $sig['field'] }}">
                                            <input type="hidden" name="current_tab" value="2">
                                            <select name="status" class="filter-select">
                                                <option value="pending"     @selected($current==='pending')>Pending</option>
                                                <option value="for signature" @selected($current==='for signature')>For Signature</option>
                                                <option value="approved"    @selected($current==='approved')>Approved</option>
                                                <option value="disapproved" @selected($current==='disapproved')>Disapproved</option>
                                            </select>
                                            <input type="text" name="remark" class="form-control"
                                                style="flex:1; min-width:180px;"
                                                placeholder="Remark (optional)"
                                                value="{{ $activity->{$sig['remark']} ?? '' }}">
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
                                $locked  = $financeLocked[$sig['field']] || !$isForFinance;
                                $current = $activity->{$sig['field']} ?? 'pending';
                            @endphp
                            <div class="signatory-card {{ $locked ? 'signatory-card--locked' : '' }}">
                                <div class="signatory-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fas {{ $locked ? 'fa-lock' : 'fa-user-tie' }}"
                                            style="color:{{ $locked ? '#94a3b8' : '#16a34a' }}; font-size:12px;"></i>
                                        <span style="font-weight:600; font-size:13.5px; color:#1e293b;">
                                            {{ $sig['role'] }}
                                        </span>
                                    </div>
                                    <span class="badge {{ $approvalBadgeClass($current) }}">
                                        {{ ucfirst($current) }}
                                    </span>
                                </div>
                                @if($activity->{$sig['remark']})
                                    <div class="signatory-remark">
                                        <i class="fas fa-comment-dots" style="color:#d97706;"></i>
                                        {{ $activity->{$sig['remark']} }}
                                    </div>
                                @endif
                                <div class="signatory-body">
                                    @if($locked)
                                        <span class="td-muted" style="font-size:12px;">
                                            <i class="fas fa-lock"></i>
                                            {{ !$isForFinance
                                                ? 'Unlocks after all signatories are approved.'
                                                : 'Waiting for previous signatory.' }}
                                        </span>
                                    @else
                                        <form action="{{ route('dean_osa.approval.approve', $activity->id) }}"
                                            method="POST"
                                            style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="approver" value="{{ $sig['field'] }}">
                                            <input type="hidden" name="current_tab" value="2">
                                            <select name="status" class="filter-select">
                                                <option value="pending"     @selected($current==='pending')>Pending</option>
                                                <option value="for signature" @selected($current==='for signature')>For Signature</option>
                                                <option value="approved"    @selected($current==='approved')>Approved</option>
                                                <option value="disapproved" @selected($current==='disapproved')>Disapproved</option>
                                            </select>
                                            <input type="text" name="remark" class="form-control"
                                                style="flex:1; min-width:180px;"
                                                placeholder="Remark (optional)"
                                                value="{{ $activity->{$sig['remark']} ?? '' }}">
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
            <div id="tab-3" style="display:none;">

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

                    <div class="show-section">
                        <div class="show-section-header green">
                            <i class="fas fa-upload"></i> Upload Approved SARF
                        </div>
                        <div style="padding:16px 20px;">
                            <form action="{{ route('dean_osa.approval.document.store', $activity->id) }}"
                                method="POST" enctype="multipart/form-data"
                                style="display:flex; flex-direction:column; gap:12px;">
                                @csrf
                                <input type="hidden" name="current_tab" value="3">

                                <p class="td-muted" style="margin:0;">
                                    Check the SARF types needed and upload the corresponding PDF file.
                                </p>

                                @foreach($sarfLabels as $type => $label)
                                    <div class="attachment-row">
                                        <label class="attachment-check">
                                            <input type="checkbox" name="types[]" value="{{ $type }}"
                                                id="approved_check_{{ $type }}"
                                                onchange="toggleApprovedFile('{{ $type }}', this.checked)"
                                                @checked(old('types') && in_array($type, old('types')))>
                                            <span class="sarf-badge">{{ $type }}</span>
                                            <span class="sarf-label">{{ $label }}</span>
                                        </label>
                                        <div class="file-upload-wrap" id="approved-upload-wrap-{{ $type }}"
                                            style="display:{{ old('types') && in_array($type, old('types')) ? 'flex' : 'none' }};">
                                            <input type="file" name="file_{{ $type }}"
                                                id="approved_file_{{ $type }}" accept=".pdf"
                                                onchange="updateApprovedFileName('{{ $type }}', this)">
                                            <label for="approved_file_{{ $type }}" class="file-label">
                                                <i class="fas fa-upload"></i> Choose PDF
                                            </label>
                                            <span class="file-name-display" id="approved_fname_{{ $type }}">
                                                @if($docs->has($type))
                                                    Current: {{ $docs[$type]->original_filename }}
                                                @else
                                                    No file chosen
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    @error('file_' . $type)
                                        <div style="margin-top:-6px; color:#b91c1c; font-size:12px;">{{ $message }}</div>
                                    @enderror
                                @endforeach

                                <div style="display:flex; justify-content:flex-end;">
                                    <button type="submit" class="btn btn-add" style="font-size:12px;">
                                        <i class="fas fa-save"></i> Save Approved SARF
                                    </button>
                                </div>
                            </form>
                            @error('approved_doc')
                                <div style="margin-top:8px; color:#b91c1c; font-size:12px;">{{ $message }}</div>
                            @enderror
                            @error('types')
                                <div style="margin-top:8px; color:#b91c1c; font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
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

function showTab(n) {
    for (let i = 1; i <= TOTAL_TABS; i++) {
        const pane = document.getElementById('tab-' + i);
        if (pane) pane.style.display = (i === n) ? 'block' : 'none';
        const btn = document.getElementById('step-indicator-' + i);
        if (btn) btn.classList.toggle('active', i === n);
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggleApprovedFile(type, checked) {
    const wrap = document.getElementById('approved-upload-wrap-' + type);
    if (!wrap) return;
    wrap.style.display = checked ? 'flex' : 'none';
}

function updateApprovedFileName(type, input) {
    const display = document.getElementById('approved_fname_' + type);
    if (!display) return;
    display.textContent = input?.files?.length ? input.files[0].name : 'No file chosen';
}

document.addEventListener('DOMContentLoaded', () => {
    const tabFromUrl = Number(new URLSearchParams(window.location.search).get('tab'));
    if ([1, 2, 3].includes(tabFromUrl)) {
        showTab(tabFromUrl);
    }
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
</script>
@endsection
