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

    .btn-quick-action--reschedule {
        background: #fffbeb;
        color: #b45309;
        border-color: #fbbf24;
    }
    .btn-quick-action--reschedule:hover {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }
    @media (max-width: 720px) {
        .workflow-spacer {
            display: none;
        }
    }
    .workflow-side-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-left: auto;
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
    .budget-remark-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 8px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .approved-budget-input {
        width: 150px;
        min-width: 130px;
        flex: 0 0 150px;
    }
    .approval-row-form {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .approval-row-form .filter-select {
        flex: 0 0 150px;
    }
    .approval-remark-input {
        flex: 1 1 260px;
        min-width: 220px;
    }
    .approval-budget-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        padding: 0 10px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .approved-budget-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 16px;
        background: #f0fdf4;
        border-bottom: 1px solid #bbf7d0;
        color: #15803d;
    }
    .approved-budget-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 700;
    }
    .approved-budget-value {
        font-size: 13px;
        font-weight: 800;
        color: #166534;
        white-space: nowrap;
    }

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

    .approved-upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
    }
    .approved-upload-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }
    .approved-upload-card.is-selected {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
    }
    .approved-upload-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }
    .approved-upload-title {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .approved-upload-title strong {
        font-size: 13px;
        color: #0f172a;
    }
    .approved-upload-title span {
        font-size: 11.5px;
        color: #94a3b8;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .approved-dropzone {
        margin: 14px;
        min-height: 150px;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        display: none;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 18px;
        background: #fff;
    }
    .approved-dropzone.is-visible {
        display: flex;
    }
    .approved-dropzone input[type="file"] {
        width: 1px;
        height: 1px;
        opacity: 0;
        position: absolute;
        pointer-events: none;
    }
    .approved-dropzone-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .approved-dropzone-inner i {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 16px;
    }
    .approved-dropzone-main {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }
    .approved-dropzone-sub {
        font-size: 11.5px;
        color: #94a3b8;
    }
    .approved-file-chip {
        max-width: 100%;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        padding: 7px 10px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .approved-file-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .approved-remark-box {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 14px;
        background: #f8fafc;
    }
    .approved-remark-box textarea {
        width: 100%;
        min-height: 96px;
        resize: vertical;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        font-family: inherit;
        font-size: 13px;
        color: #1e293b;
    }

    .document-check-row {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0 14px 14px;
    }
    .document-check-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #3b82f6;
        background: #dbeafe;
        border-radius: 20px;
        padding: 6px 12px;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .document-check-btn:hover {
        background: #bfdbfe;
        color: #1d4ed8;
    }
    .document-download-btn {
        color: #15803d;
        background: #dcfce7;
    }
    .document-download-btn:hover {
        background: #bbf7d0;
        color: #166534;
    }
    .document-preview-btn {
        display: none;
        color: #7c3aed;
        background: #ede9fe;
    }
    .document-preview-btn.is-visible {
        display: inline-flex;
    }
    .document-preview-btn:hover {
        background: #ddd6fe;
        color: #6d28d9;
    }

    @media (max-width: 640px) {
        .show-grid { grid-template-columns: 1fr; }
        .show-field.full,
        .show-field:nth-child(odd) { border-right: none; }
        .step-indicators { flex-direction: column; }
    }

    /* Rescheduling pulse animation */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.4; }
    }
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
                            <button type="button" class="btn-quick-action btn-quick-action--reschedule" onclick="toggleRescheduleForm()">
                                <i class="fas fa-calendar-plus"></i> Request Reschedule
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
                 RESCHEDULE SECTION
            ══════════════════════════════════════ --}}
            @php $hasPendingReschedule = $activity->reschedule_status === 'pending'; @endphp

            {{-- Freeze banner when reschedule is pending --}}
            @if($hasPendingReschedule)
            <div style="display:flex; align-items:flex-start; gap:14px; padding:16px 20px;
                background:#fef3c7; border:1.5px solid #fbbf24; border-radius:12px;
                margin-bottom:20px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#fffbeb;
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-calendar-exclamation" style="color:#d97706; font-size:18px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:800; font-size:14px; color:#92400e; margin-bottom:4px;">
                        <i class="fas fa-pause-circle"></i> Reschedule Pending — Approvals Paused
                    </div>
                    <div style="font-size:13px; color:#78350f; line-height:1.6;">
                        A reschedule request has been submitted. All signatory approvals are <strong>frozen</strong>
                        until this reschedule is approved or rejected. Existing progress will <strong>not</strong> be reset.
                    </div>

                    <div style="margin-top:14px; padding:14px 16px; background:#fff; border:1px solid #fde68a;
                        border-radius:10px;">
                        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:10px;">
                            <div>
                                <div class="show-label">Proposed Date</div>
                                <div class="show-value" style="font-weight:700; color:#b45309;">
                                    <i class="fas fa-calendar-alt" style="font-size:12px;"></i>
                                    {{ $activity->reschedule_date?->format('M j, Y') }}
                                </div>
                            </div>
                            @if(filled($activity->reschedule_time))
                            <div>
                                <div class="show-label">Proposed Time</div>
                                <div class="show-value">{{ $activity->reschedule_time }}</div>
                            </div>
                            @endif
                            @if(filled($activity->reschedule_venue))
                            <div>
                                <div class="show-label">Proposed Venue</div>
                                <div class="show-value">{{ $activity->reschedule_venue }}</div>
                            </div>
                            @endif
                            <div>
                                <div class="show-label">Requested</div>
                                <div class="show-value">{{ $activity->reschedule_requested_at?->format('M j, Y g:i A') }}</div>
                            </div>
                        </div>
                        <div style="margin-bottom:14px;">
                            <div class="show-label">Reason</div>
                            <div class="show-value">{{ $activity->reschedule_reason }}</div>
                        </div>

                        {{-- Approve / Reject forms --}}
                        <div style="display:flex; gap:10px; flex-wrap:wrap; padding-top:10px; border-top:1px solid #fde68a;">
                            <div style="flex:1; min-width:200px;">
                                <input type="text" id="reschedule-remarks-input" class="form-control"
                                    placeholder="Optional remarks…" style="font-size:12.5px;">
                            </div>
                            <form action="{{ route('dean_osa.approval.reschedule.approve', $activity->id) }}"
                                method="POST" style="display:inline;" id="reschedule-approve-form">
                                @csrf
                                <input type="hidden" name="reschedule_remarks" id="reschedule-approve-remarks">
                                <button type="submit" class="btn btn-success"
                                    onclick="document.getElementById('reschedule-approve-remarks').value = document.getElementById('reschedule-remarks-input').value;"
                                    style="font-size:12.5px;">
                                    <i class="fas fa-check"></i> Approve Reschedule
                                </button>
                            </form>
                            <form action="{{ route('dean_osa.approval.reschedule.reject', $activity->id) }}"
                                method="POST" style="display:inline;" id="reschedule-reject-form">
                                @csrf
                                <input type="hidden" name="reschedule_remarks" id="reschedule-reject-remarks">
                                <button type="submit" class="btn btn-danger"
                                    onclick="document.getElementById('reschedule-reject-remarks').value = document.getElementById('reschedule-remarks-input').value;"
                                    style="font-size:12.5px;">
                                    <i class="fas fa-times"></i> Reject Reschedule
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Reschedule request form (hidden by default) --}}
            @if(!$hasPendingReschedule && in_array($activity->status, ['ongoing','for approval','for approval finance']))
            <div id="reschedule-form-panel" style="display:none; margin-bottom:24px;">
                <div style="border:1.5px solid #93c5fd; border-radius:12px; overflow:hidden;">
                    <div style="display:flex; align-items:center; gap:10px; padding:14px 18px;
                        background:#eff6ff; border-bottom:1px solid #bfdbfe;">
                        <i class="fas fa-calendar-alt" style="color:#2563eb; font-size:14px;"></i>
                        <span style="font-size:14px; font-weight:700; color:#1e40af;">Request Reschedule</span>
                        <button type="button" onclick="toggleRescheduleForm()" style="margin-left:auto;
                            background:none; border:none; color:#64748b; cursor:pointer; font-size:16px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div style="padding:18px 20px;">
                        <form action="{{ route('dean_osa.approval.reschedule.request', $activity->id) }}" method="POST">
                            @csrf
                            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:14px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">New Date <span style="color:#dc2626;">*</span></label>
                                    <input type="date" name="reschedule_date" class="form-control" required
                                        value="{{ old('reschedule_date') }}" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">New Time</label>
                                    <input type="text" name="reschedule_time" class="form-control"
                                        placeholder="e.g. 9:00 AM - 5:00 PM" value="{{ old('reschedule_time') }}">
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">New Venue</label>
                                    <input type="text" name="reschedule_venue" class="form-control"
                                        placeholder="Leave blank to keep current" value="{{ old('reschedule_venue') }}">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:14px;">
                                <label class="form-label">Reason for Rescheduling <span style="color:#dc2626;">*</span></label>
                                <textarea name="reschedule_reason" class="form-control" rows="3" required
                                    placeholder="Explain why this activity needs to be rescheduled…"
                                    style="resize:vertical;">{{ old('reschedule_reason') }}</textarea>
                            </div>
                            <div style="display:flex; gap:10px; justify-content:flex-end;">
                                <button type="button" onclick="toggleRescheduleForm()" class="btn btn-filter">Cancel</button>
                                <button type="submit" class="btn btn-add">
                                    <i class="fas fa-paper-plane"></i> Submit Reschedule Request
                                </button>
                            </div>
                        </form>
                    </div>
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
                                    <a href="{{ route('dean_osa.sarf-documents.show', $docs[$type]) }}"
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

/* ── Reschedule form toggle ── */
function toggleRescheduleForm() {
    const panel = document.getElementById('reschedule-form-panel');
    if (panel) {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        if (panel.style.display === 'block') {
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}
</script>
@endsection
