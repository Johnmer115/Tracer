@extends($layout ?? 'Dean_OSA.layouts.layout')

@section('title', 'SARF Tracker Details | SARF Tracking')
@section('page-title', 'SARF Tracker')

@section('content')
<section class="panel" style="padding: 25px;">
    @php
        $statusClass = match($activity->status) {
            'pending' => 'b-pending',
            'ongoing', 'for approval', 'for approval finance' => 'b-ongoing',
            'approved' => 'b-approved',
            'completed' => 'b-completed',
            'cancelled' => 'b-inactive',
            'for revision' => 'b-revision',
            default => 'b-pending',
        };

        $mainSignatories = [
            ['field' => 'approval_dean_sa', 'remark' => 'remarks_dean_sa', 'budget' => 'budget_dean_sa', 'time' => 'approved_at_dean_sa', 'role' => 'Dean for Student Affairs'],
            ['field' => 'approval_avp_sps', 'remark' => 'remarks_avp_sps', 'budget' => 'budget_avp_sps', 'time' => 'approved_at_avp_sps', 'role' => 'Asst. VP for Student Personnel Services'],
            ['field' => 'approval_dir_basic_ed', 'remark' => 'remarks_dir_basic_ed', 'budget' => 'budget_dir_basic_ed', 'time' => 'approved_at_dir_basic_ed', 'role' => 'Director for Basic Education'],
            ['field' => 'approval_vp_acad', 'remark' => 'remarks_vp_acad', 'budget' => 'budget_vp_acad', 'time' => 'approved_at_vp_acad', 'role' => 'VP for Academic Affairs'],
            ['field' => 'approval_vp_hrd_legal', 'remark' => 'remarks_vp_hrd_legal', 'budget' => 'budget_vp_hrd_legal', 'time' => 'approved_at_vp_hrd_legal', 'role' => 'VP for HRD / Legal'],
        ];
        $financeSignatories = [
            ['field' => 'approval_auditing', 'remark' => 'remarks_auditing', 'budget' => 'budget_auditing', 'time' => 'approved_at_auditing', 'role' => 'Auditing'],
            ['field' => 'approval_comptroller_initial', 'remark' => 'remarks_comptroller_initial', 'budget' => 'budget_comptroller_initial', 'time' => 'approved_at_comptroller_initial', 'role' => 'Comptroller'],
            ['field' => 'approval_finance_initial', 'remark' => 'remarks_finance_initial', 'budget' => 'budget_finance_initial', 'time' => 'approved_at_finance_initial', 'role' => 'Finance'],
            ['field' => 'approval_osa_finance', 'remark' => 'remarks_osa_finance', 'budget' => 'budget_osa_finance', 'time' => 'approved_at_osa_finance', 'role' => 'OSA Finance'],
            ['field' => 'approval_finance_final', 'remark' => 'remarks_finance_final', 'budget' => 'budget_finance_final', 'time' => 'approved_at_finance_final', 'role' => 'Finance Final'],
            ['field' => 'approval_comptroller_final', 'remark' => 'remarks_comptroller_final', 'budget' => 'budget_comptroller_final', 'time' => 'approved_at_comptroller_final', 'role' => 'Comptroller Final'],
        ];

        $levels = is_array($activity->level) ? $activity->level : (filled($activity->level) ? [$activity->level] : []);
        $departments = is_array($activity->department) ? $activity->department : (filled($activity->department) ? [$activity->department] : []);
        $orgs = is_array($activity->organizations) ? $activity->organizations : (filled($activity->organizations) ? [$activity->organizations] : []);
        $needsBasicEd = collect($levels)->contains(function ($level) {
            $level = Str::lower((string) $level);
            return Str::contains($level, ['elementary', 'junior high', 'senior high', 'basic', 'all levels']);
        });
        $needsFinance = $activity->funds === 'With Budget';
        $needsLegal = $activity->waiver_consent === 'With';
        $mainSignatories = collect($mainSignatories)
            ->filter(fn($sig) => $sig['field'] !== 'approval_dir_basic_ed' || $needsBasicEd)
            ->filter(fn($sig) => $sig['field'] !== 'approval_vp_hrd_legal' || $needsLegal)
            ->values();
        $financeSignatories = $needsFinance ? collect($financeSignatories)->values() : collect();
        $allSignatories = $mainSignatories->merge($financeSignatories);
        $hasDisapproval = $allSignatories->contains(fn($sig) => ($activity->{$sig['field']} ?? 'pending') === 'disapproved');
        $canViewApprovedBudget = auth()->user()?->usertype === 'Dean_OSA';

        $rescheduleApprovalFields = [
            'approval_dean_sa' => 'reschedule_approval_dean_sa',
            'approval_avp_sps' => 'reschedule_approval_avp_sps',
            'approval_dir_basic_ed' => 'reschedule_approval_dir_basic_ed',
            'approval_vp_acad' => 'reschedule_approval_vp_acad',
            'approval_vp_hrd_legal' => 'reschedule_approval_vp_hrd_legal',
            'approval_auditing' => 'reschedule_approval_auditing',
            'approval_comptroller_initial' => 'reschedule_approval_comptroller_initial',
            'approval_finance_initial' => 'reschedule_approval_finance_initial',
            'approval_osa_finance' => 'reschedule_approval_osa_finance',
            'approval_finance_final' => 'reschedule_approval_finance_final',
            'approval_comptroller_final' => 'reschedule_approval_comptroller_final',
        ];
        $rescheduleRemarkFields = [
            'approval_dean_sa' => 'reschedule_remarks_dean_sa',
            'approval_avp_sps' => 'reschedule_remarks_avp_sps',
            'approval_dir_basic_ed' => 'reschedule_remarks_dir_basic_ed',
            'approval_vp_acad' => 'reschedule_remarks_vp_acad',
            'approval_vp_hrd_legal' => 'reschedule_remarks_vp_hrd_legal',
            'approval_auditing' => 'reschedule_remarks_auditing',
            'approval_comptroller_initial' => 'reschedule_remarks_comptroller_initial',
            'approval_finance_initial' => 'reschedule_remarks_finance_initial',
            'approval_osa_finance' => 'reschedule_remarks_osa_finance',
            'approval_finance_final' => 'reschedule_remarks_finance_final',
            'approval_comptroller_final' => 'reschedule_remarks_comptroller_final',
        ];
        $rescheduleTimeFields = [
            'approval_dean_sa' => 'reschedule_approved_at_dean_sa',
            'approval_avp_sps' => 'reschedule_approved_at_avp_sps',
            'approval_dir_basic_ed' => 'reschedule_approved_at_dir_basic_ed',
            'approval_vp_acad' => 'reschedule_approved_at_vp_acad',
            'approval_vp_hrd_legal' => 'reschedule_approved_at_vp_hrd_legal',
            'approval_auditing' => 'reschedule_approved_at_auditing',
            'approval_comptroller_initial' => 'reschedule_approved_at_comptroller_initial',
            'approval_finance_initial' => 'reschedule_approved_at_finance_initial',
            'approval_osa_finance' => 'reschedule_approved_at_osa_finance',
            'approval_finance_final' => 'reschedule_approved_at_finance_final',
            'approval_comptroller_final' => 'reschedule_approved_at_comptroller_final',
        ];
        $rescheduleApprovedByFields = [
            'approval_dean_sa' => 'reschedule_approved_by_dean_sa',
            'approval_avp_sps' => 'reschedule_approved_by_avp_sps',
            'approval_dir_basic_ed' => 'reschedule_approved_by_dir_basic_ed',
            'approval_vp_acad' => 'reschedule_approved_by_vp_acad',
            'approval_vp_hrd_legal' => 'reschedule_approved_by_vp_hrd_legal',
            'approval_auditing' => 'reschedule_approved_by_auditing',
            'approval_comptroller_initial' => 'reschedule_approved_by_comptroller_initial',
            'approval_finance_initial' => 'reschedule_approved_by_finance_initial',
            'approval_osa_finance' => 'reschedule_approved_by_osa_finance',
            'approval_finance_final' => 'reschedule_approved_by_finance_final',
            'approval_comptroller_final' => 'reschedule_approved_by_comptroller_final',
        ];
        $rescheduleApproverIds = $allSignatories
            ->map(fn($sig) => $activity->{$rescheduleApprovedByFields[$sig['field']]} ?? null)
            ->filter()
            ->unique()
            ->values();
        $rescheduleApproverNames = $rescheduleApproverIds->isEmpty()
            ? collect()
            : \App\Models\Account::whereIn('id', $rescheduleApproverIds)->pluck('username', 'id');

        $isReschedulingActive = filled($activity->reschedule_requested_at) && $activity->reschedule_status !== 'approved';
        $isReschedulingDone = filled($activity->reschedule_requested_at) && $activity->reschedule_status === 'approved';
        $isForApprovalRescheduling = $activity->status === 'for approval for rescheduling';

        $pipeline = [
            [
                'label' => 'For Approval',
                'active' => $activity->status === 'for approval',
                'done' => in_array($activity->status, ['for approval finance', 'approved', 'completed', 'for approval for rescheduling']),
            ],
            [
                'label' => 'Finance Review',
                'active' => $activity->status === 'for approval finance',
                'done' => in_array($activity->status, ['approved', 'completed', 'for approval for rescheduling']),
            ],
            [
                'label' => 'Approved',
                'active' => $activity->status === 'approved' && !$isReschedulingActive && !$isReschedulingDone,
                'done' => $activity->status === 'completed' || $isForApprovalRescheduling || $isReschedulingActive || $isReschedulingDone,
            ],
        ];

        if (filled($activity->reschedule_requested_at)) {
            $pipeline[] = [
                'label' => 'Rescheduling',
                'active' => $isReschedulingActive,
                'done' => $isReschedulingDone || $activity->status === 'completed',
            ];
        }

        $pipeline[] = [
            'label' => 'Completed',
            'active' => $activity->status === 'completed',
            'done' => $activity->status === 'completed',
        ];

        $approvalIcon = fn($v) => match($v ?? 'pending') {
            'approved' => ['fas fa-check-circle', '#16a34a', '#dcfce7', '#86efac', 'Approved'],
            'for signature' => ['fas fa-pen-nib', '#014ea8', '#dbeafe', '#93c5fd', 'For Signature'],
            'disapproved' => ['fas fa-times-circle', '#dc2626', '#fef2f2', '#fca5a5', 'Disapproved'],
            default => ['fas fa-clock', '#64748b', '#f1f5f9', '#e2e8f0', 'Pending'],
        };
    @endphp

    <!-- PRINT HEADER PLACEHOLDER -->
    <div class="print-only print-header" style="position: fixed; top: 0; left: 0; right: 0; width: 100%; background: #fff; z-index: 1000; padding: 4px 0.8in 0 0.8in; margin: 0; background: #fff;">
        <div style="padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; gap: 16px; border-bottom: 3px solid #000;">
            <img src="{{ asset('image/logo/arellano_logo.png') }}" style="width:60px; height:auto;">
            <div style="text-align: center;">
                <div style="font-size:15px; font-weight:800; text-transform:uppercase; color:#000;">ARELLANO UNIVERSITY</div>
                <div style="font-size:12px; font-weight:700; color:#000;">OFFICE FOR STUDENT AFFAIRS</div>
                <div style="font-size:9px; color:#374151; font-weight:400;">2600 Legarda Street, Sampaloc, Manila</div>
            </div>
            <img src="{{ asset('image/logo/au_osa_logo.png') }}" style="width:60px; height:auto;">
        </div>
        <div style="height:2px; background:#000; margin-top:3px;"></div>
    </div>
    <!-- /PRINT HEADER PLACEHOLDER -->

    <!-- PRINT FOOTER PLACEHOLDER -->
    <div class="print-only print-footer" style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; background: #fff; z-index: 1000; padding: 0 0.8in; margin: 0;">
        <div style="display:flex;align-items:center;gap:22px;padding:8px 0 5px 0;">
            <!-- FOOTER LEFT -->
            <div class="print-footer-left" style="font-size:20px;font-weight:900;color:#000;white-space:nowrap;">
                #oneArellano
            </div>
            <!-- /FOOTER LEFT -->

            <!-- FOOTER CENTER -->
            <div class="print-footer-center" style="text-align:left;flex:0 0 auto;">
                <img src="{{ asset('image/logo/osa_logo.png') }}" style="width:128px;height:auto;display:block;">
            </div>
            <!-- /FOOTER CENTER -->

            <!-- FOOTER RIGHT -->
            <div class="print-footer-right" style="display:flex;flex-direction:column;gap:5px;font-size:13px;color:#000;margin-left:auto;min-width:380px;">
                <div style="height:3px;background:#000;"></div>
                <div style="height:1.5px;background:#000;margin-top:-2px;margin-bottom:5px;"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:7px 24px;">
                    <span style="display:flex;align-items:center;gap:7px;white-space:nowrap;">
                        <img src="{{ asset('image/logo/globe.logo.png') }}" style="width:17px;height:17px;">
                        www.arellano.edu.ph
                    </span>
                    <span style="display:flex;align-items:center;gap:7px;white-space:nowrap;">
                        <img src="{{ asset('image/logo/gmail_logo.png') }}" style="width:17px;height:17px;">
                        main.osa@arellano.edu.ph
                    </span>
                    <span style="display:flex;align-items:center;gap:7px;white-space:nowrap;">
                        <img src="{{ asset('image/logo/call_logo.png') }}" style="width:17px;height:17px;">
                        (02) 8 734 7371 to 75 loc. 206
                    </span>
                    <span style="display:flex;align-items:center;gap:7px;white-space:nowrap;">
                        <i class="fab fa-facebook" style="font-size:17px;color:#000;"></i>
                        <i class="fab fa-instagram" style="font-size:17px;color:#000;"></i>
                        <span>ArellanoUniversityOSA</span>
                    </span>
                </div>
            </div>
            <!-- /FOOTER RIGHT -->
        </div>
    </div>
    <!-- /PRINT FOOTER PLACEHOLDER -->

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-satellite-dish"></i> {{ $activity->title }}
                <span style="margin-left:6px;">@include('partials.sarf-status-badge', ['activity' => $activity])</span>
            </div>
            <div class="panel-controls">
                <span class="td-muted" style="font-size:12px;">
                    <i class="fas fa-hashtag"></i> {{ $activity->code }}
                    &nbsp;|&nbsp;
                    <i class="fas fa-calendar"></i> {{ $activity->date_of_activity?->format('M d, Y') ?? 'N/A' }}
                </span>
                <button type="button"
                    id="downloadTracerPdf"
                    class="btn btn-add no-pdf tracer-print-btn"
                    style="cursor: pointer; margin-right: 5px;"
                    data-filename="{{ Str::slug($activity->code ?: $activity->title) }}-tracer.pdf">
                    <i class="fas fa-file-pdf"></i> Tracer Print/Download
                </button>
                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.tracer.index') }}" class="btn btn-filter no-pdf">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div id="tracerPdfContent" style="padding: 24px; box-sizing: border-box; max-width: 100%; overflow: hidden;" class="print-padding tracer-pdf-content">
            <div class="print-only print-title-code">
                <span>{{ $activity->title }}</span>
                <span>|</span>
                <span>{{ $activity->code ?: 'No Code' }}</span>
            </div>

            <div class="print-page print-first-page">
            {{-- ===== Details first ===== --}}
            <div class="print-details" style="padding:16px;background:#f9fafb;border:1px solid var(--border);border-radius:10px;margin-bottom:20px;">
                <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151;">
                    <i class="fas fa-info-circle"></i> Activity Details
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;">
                    @php
                        $hasVenue = in_array($activity->mode_of_conduct, ['Face to Face', 'Hybrid'], true);
                        $hasPlatform = in_array($activity->mode_of_conduct, ['Online', 'Hybrid'], true);
                        $hasBudgetInfo = in_array($activity->funds, ['With Budget', 'ATC'], true);
                        $detailRows = array_filter([
                            ['Branch', $activity->branch->name ?? null],
                            ['School Year', $activity->school_year_code],
                            ['Date', $activity->date_of_activity?->format('M d, Y')],
                            ['Time', $activity->time_of_activity],
                            $hasVenue ? ['Venue', trim(($activity->venue ?? '') . ($activity->venue_type ? " ({$activity->venue_type})" : ''))] : null,
                            $hasPlatform ? ['Platform', $activity->platform] : null,
                            ['Type', $activity->type_of_activity],
                            ['Mode', $activity->mode_of_conduct],
                            ['Level', count($levels) ? implode(', ', $levels) : null],
                            ['Department', count($departments) ? implode(', ', $departments) : null],
                            ['Organization', count($orgs) ? implode(', ', $orgs) : null],
                            ['Funds', $activity->funds],
                            $hasBudgetInfo && $activity->amount !== null ? ['Requested Budget', 'PHP ' . number_format($activity->amount, 2)] : null,
                            $activity->funds === 'With Budget' ? ['Source', $activity->source] : null,
                            $hasBudgetInfo ? ['Canteen', $activity->canteen] : null,
                            $hasBudgetInfo ? ['Procurement', $activity->procurement] : null,
                            ['Submitted', $activity->created_at?->format('M d, Y g:i A')],
                        ], fn ($row) => $row && filled($row[1]));
                    @endphp
                    @foreach($detailRows as [$label, $value])
                        <div>
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">{{ $label }}</div>
                            <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== Tracer middle ===== --}}
            @if(!in_array($activity->status, ['cancelled', 'for revision']))
                <div class="pipeline-tracker">
                    @foreach($pipeline as $pi => $ps)
                        @php $done = $ps['done']; $active = $ps['active']; @endphp
                        <div class="pipeline-step">
                            <div class="pipeline-step-inner">
                                <div style="width:34px;height:34px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;
                                    background:{{ $done ? '#dcfce7' : ($active ? 'var(--primary)' : '#e2e8f0') }};
                                    color:{{ $done ? '#16a34a' : ($active ? '#fff' : '#94a3b8') }};
                                    font-size:13px;box-shadow:{{ $active ? '0 0 0 3px rgba(1,78,168,.15)' : 'none' }};">
                                    <i class="fas {{ $done ? 'fa-check' : 'fa-circle' }}"></i>
                                </div>
                                <div style="font-size:9px;font-weight:{{ $active ? 700 : 500 }};color:{{ $done ? '#16a34a' : ($active ? 'var(--primary)' : '#94a3b8') }};">
                                    {{ $ps['label'] }}
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="pipeline-connector" style="background:{{ $done ? '#86efac' : '#e2e8f0' }};"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($hasDisapproval)
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    <i class="fas fa-times-circle"></i>
                    <strong>Disapproved:</strong> One or more signatories disapproved this SARF. Review the remarks below.
                </div>
            @endif

            <div style="margin-bottom:12px;">
                <div style="font-weight:700;font-size:14px;margin-bottom:16px;color:#374151;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-stamp" style="color:var(--primary);"></i> Approval Tracer
                </div>

                @unless($mainSignatories->isEmpty())
                    <div class="approval-group" style="margin-bottom:24px;">
                        <div class="approval-group-title" style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                            For Approval
                        </div>
                        <div class="approval-track" style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                            <div class="approval-line" style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                            @foreach($mainSignatories as $sig)
                                @php
                                    $statusVal = $activity->{$sig['field']} ?? 'pending';
                                    [$icon, $color, $bg, $border, $label] = $approvalIcon($statusVal);
                                    $approvedAt = $activity->{$sig['time']} ?? null;
                                    $approvedBudget = $activity->{$sig['budget']} ?? null;
                                    $statusClass = match($statusVal) {
                                        'approved' => 'status-approved',
                                        'for signature' => 'status-for-signature',
                                        'disapproved' => 'status-disapproved',
                                        default => 'status-pending',
                                    };
                                @endphp
                                <div class="approval-card {{ $statusClass }}">
                                    <div class="approval-dot" style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
                                    <i class="{{ $icon }}" style="color:{{ $color }};font-size:16px;margin-top:2px;flex-shrink:0;"></i>
                                    <div style="flex:1;">
                                        <div style="font-weight:600;font-size:13px;color:#1e293b;">{{ $sig['role'] }}</div>
                                        <div style="font-size:12px;color:{{ $color }};font-weight:500;margin-top:2px;">
                                            {{ $label }}
                                        </div>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#475569;">
                                                <i class="fas fa-clock"></i>
                                                Approved time:
                                                <strong>{{ $approvedAt ? $approvedAt->format('M d, Y g:i A') : 'Not recorded' }}</strong>
                                            </span>
                                            @if($canViewApprovedBudget && $approvedBudget !== null)
                                                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#15803d;">
                                                    <i class="fas fa-wallet"></i>
                                                    Approved budget:
                                                    <strong>PHP {{ number_format($approvedBudget, 2) }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                        @if($activity->{$sig['remark']})
                                            <div style="margin-top:6px;padding:6px 10px;background:rgba(255,255,255,.7);border-radius:6px;font-size:12px;color:#374151;border-left:3px solid {{ $color }};">
                                                <i class="fas fa-comment-dots" style="margin-right:4px;"></i>{{ $activity->{$sig['remark']} }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endunless
            </div>
            </div>

            @if(!$financeSignatories->isEmpty() || filled($activity->reschedule_requested_at))
                <div class="print-page print-second-page">
                    @unless($financeSignatories->isEmpty())
                        <div class="approval-group print-finance-group" style="margin-bottom:24px;">
                            <div class="approval-group-title" style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                                <span class="no-print">Finance Approval</span>
                                <span class="print-only print-inline">Finance Tracer</span>
                            </div>
                            <div class="approval-track" style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                                <div class="approval-line" style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                                @foreach($financeSignatories as $sig)
                                    @php
                                        $statusVal = $activity->{$sig['field']} ?? 'pending';
                                        [$icon, $color, $bg, $border, $label] = $approvalIcon($statusVal);
                                        $approvedAt = $activity->{$sig['time']} ?? null;
                                        $approvedBudget = $activity->{$sig['budget']} ?? null;
                                        $statusClass = match($statusVal) {
                                            'approved' => 'status-approved',
                                            'for signature' => 'status-for-signature',
                                            'disapproved' => 'status-disapproved',
                                            default => 'status-pending',
                                        };
                                    @endphp
                                    <div class="approval-card {{ $statusClass }}">
                                        <div class="approval-dot" style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
                                        <i class="{{ $icon }}" style="color:{{ $color }};font-size:16px;margin-top:2px;flex-shrink:0;"></i>
                                        <div style="flex:1;">
                                            <div style="font-weight:600;font-size:13px;color:#1e293b;">{{ $sig['role'] }}</div>
                                            <div style="font-size:12px;color:{{ $color }};font-weight:500;margin-top:2px;">
                                                {{ $label }}
                                            </div>
                                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#475569;">
                                                    <i class="fas fa-clock"></i>
                                                    Approved time:
                                                    <strong>{{ $approvedAt ? $approvedAt->format('M d, Y g:i A') : 'Not recorded' }}</strong>
                                                </span>
                                                @if($canViewApprovedBudget && $approvedBudget !== null)
                                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#15803d;">
                                                        <i class="fas fa-wallet"></i>
                                                        Approved budget:
                                                        <strong>PHP {{ number_format($approvedBudget, 2) }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                            @if($activity->{$sig['remark']})
                                                <div style="margin-top:6px;padding:6px 10px;background:rgba(255,255,255,.7);border-radius:6px;font-size:12px;color:#374151;border-left:3px solid {{ $color }};">
                                                    <i class="fas fa-comment-dots" style="margin-right:4px;"></i>{{ $activity->{$sig['remark']} }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endunless

                    @if(filled($activity->reschedule_requested_at))
                        <div class="print-reschedule" style="margin-bottom:14px;">
                            <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-calendar-alt" style="color:var(--primary);"></i>
                                <span class="no-print">Reschedule Request</span>
                                <span class="print-only print-inline">Rescheduling Detail</span>
                            </div>
                            <div style="padding:16px;background:#f9fafb;border:1px solid var(--border);border-radius:10px;">
                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;">
                                    <div>
                                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Status</div>
                                        <div style="font-size:13px;font-weight:600;color:{{ $activity->reschedule_status === 'approved' ? '#16a34a' : ($activity->reschedule_status === 'rejected' ? '#dc2626' : '#014ea8') }};">
                                            {{ ucfirst($activity->reschedule_status ?? 'pending') }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Requested Date</div>
                                        <div style="font-size:13px;font-weight:500;color:#1e293b;">
                                            {{ $activity->reschedule_date ? \Carbon\Carbon::parse($activity->reschedule_date)->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Requested Time</div>
                                        <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $activity->reschedule_time ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Mode of Conduct</div>
                                        <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $activity->reschedule_mode ?? 'N/A' }}</div>
                                    </div>
                                    @if(filled($activity->reschedule_venue))
                                        <div>
                                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Venue</div>
                                            <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $activity->reschedule_venue }}</div>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">Requested At</div>
                                        <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $activity->reschedule_requested_at?->format('M d, Y g:i A') ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                @if(filled($activity->reschedule_reason))
                                    <div style="margin-top:12px;padding:8px 12px;background:#fff;border-left:3px solid var(--primary);border:1px solid var(--border);border-left-width:3px;border-radius:6px;font-size:12px;color:#374151;">
                                        <strong>Reason:</strong> {{ $activity->reschedule_reason }}
                                    </div>
                                @endif
                            </div>

                            <div style="font-weight:700;font-size:13px;margin:20px 0 12px;color:#374151;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-stamp" style="color:var(--primary);"></i>
                                Reschedule Approval Tracer
                            </div>

                            @unless($mainSignatories->isEmpty())
                                <div class="approval-group" style="margin-bottom:24px;">
                                    <div class="approval-group-title" style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                                        Reschedule Signatory Approvals
                                    </div>
                                    <div class="approval-track" style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                                        <div class="approval-line" style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                                        @foreach($mainSignatories as $sig)
                                            @php
                                                $statusField = $rescheduleApprovalFields[$sig['field']];
                                                $remarkField = $rescheduleRemarkFields[$sig['field']];
                                                $timeField = $rescheduleTimeFields[$sig['field']];
                                                $byField = $rescheduleApprovedByFields[$sig['field']];
                                                $statusVal = $activity->{$statusField} ?? 'pending';
                                                [$icon, $color, $bg, $border, $label] = $approvalIcon($statusVal);
                                                $approvedAt = $activity->{$timeField} ?? null;
                                                $approvedBy = $activity->{$byField} ? ($rescheduleApproverNames[$activity->{$byField}] ?? 'Account #' . $activity->{$byField}) : null;
                                                $statusClass = match($statusVal) {
                                                    'approved' => 'status-approved',
                                                    'for signature' => 'status-for-signature',
                                                    'disapproved' => 'status-disapproved',
                                                    default => 'status-pending',
                                                };
                                            @endphp
                                            <div class="approval-card {{ $statusClass }}">
                                                <div class="approval-dot" style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
                                                <i class="{{ $icon }}" style="color:{{ $color }};font-size:16px;margin-top:2px;flex-shrink:0;"></i>
                                                <div style="flex:1;">
                                                    <div style="font-weight:600;font-size:13px;color:#1e293b;">{{ $sig['role'] }}</div>
                                                    <div style="font-size:12px;color:{{ $color }};font-weight:500;margin-top:2px;">{{ $label }}</div>
                                                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#475569;">
                                                            <i class="fas fa-clock"></i>
                                                            Approved time:
                                                            <strong>{{ $approvedAt ? $approvedAt->format('M d, Y g:i A') : 'Not recorded' }}</strong>
                                                        </span>
                                                    </div>
                                                    @if($activity->{$remarkField})
                                                        <div style="margin-top:6px;padding:6px 10px;background:rgba(255,255,255,.7);border-radius:6px;font-size:12px;color:#374151;border-left:3px solid {{ $color }};">
                                                            <i class="fas fa-comment-dots" style="margin-right:4px;"></i>{{ $activity->{$remarkField} }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endunless

                            @unless($financeSignatories->isEmpty())
                                <div class="approval-group" style="margin-bottom:24px;">
                                    <div class="approval-group-title" style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                                        Reschedule Finance Approvals
                                    </div>
                                    <div class="approval-track" style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                                        <div class="approval-line" style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                                        @foreach($financeSignatories as $sig)
                                            @php
                                                $statusField = $rescheduleApprovalFields[$sig['field']];
                                                $remarkField = $rescheduleRemarkFields[$sig['field']];
                                                $timeField = $rescheduleTimeFields[$sig['field']];
                                                $byField = $rescheduleApprovedByFields[$sig['field']];
                                                $statusVal = $activity->{$statusField} ?? 'pending';
                                                [$icon, $color, $bg, $border, $label] = $approvalIcon($statusVal);
                                                $approvedAt = $activity->{$timeField} ?? null;
                                                $approvedBy = $activity->{$byField} ? ($rescheduleApproverNames[$activity->{$byField}] ?? 'Account #' . $activity->{$byField}) : null;
                                                $statusClass = match($statusVal) {
                                                    'approved' => 'status-approved',
                                                    'for signature' => 'status-for-signature',
                                                    'disapproved' => 'status-disapproved',
                                                    default => 'status-pending',
                                                };
                                            @endphp
                                            <div class="approval-card {{ $statusClass }}">
                                                <div class="approval-dot" style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
                                                <i class="{{ $icon }}" style="color:{{ $color }};font-size:16px;margin-top:2px;flex-shrink:0;"></i>
                                                <div style="flex:1;">
                                                    <div style="font-weight:600;font-size:13px;color:#1e293b;">{{ $sig['role'] }}</div>
                                                    <div style="font-size:12px;color:{{ $color }};font-weight:500;margin-top:2px;">{{ $label }}</div>
                                                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                                                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:8px;background:rgba(255,255,255,.7);font-size:11.5px;color:#475569;">
                                                            <i class="fas fa-clock"></i>
                                                            Approved time:
                                                            <strong>{{ $approvedAt ? $approvedAt->format('M d, Y g:i A') : 'Not recorded' }}</strong>
                                                        </span>
                                                    </div>
                                                    @if($activity->{$remarkField})
                                                        <div style="margin-top:6px;padding:6px 10px;background:rgba(255,255,255,.7);border-radius:6px;font-size:12px;color:#374151;border-left:3px solid {{ $color }};">
                                                            <i class="fas fa-comment-dots" style="margin-right:4px;"></i>{{ $activity->{$remarkField} }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endunless
                        </div>
                    @endif
                </div>
            @endif

            {{-- ===== Documents last ===== --}}
            <div class="no-print">
                <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151;">
                    <i class="fas fa-paperclip"></i> SARF Documents
                </div>
                @forelse($activity->sarfDocuments as $doc)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;margin-bottom:8px;border:1px solid var(--border);border-radius:8px;background:#fff;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge b-pending">{{ $doc->type }}</span>
                            <div>
                                <div style="font-weight:600;font-size:13px;">{{ $doc->original_filename ?? 'Hardcopy available' }}</div>
                                <div class="td-muted" style="font-size:11px;">Uploaded {{ $doc->created_at?->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            @if($doc->file_path)
                                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.sarf-documents.show', $doc) }}"
                                    target="_blank" class="abtn abtn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.sarf-documents.show', ['document' => $doc, 'download' => 1]) }}"
                                    class="abtn abtn-edit" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            @else
                                <span class="td-muted" style="font-size:12px;">Hardcopy only</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:16px;border:1px solid var(--border);border-radius:8px;background:#fff;" class="td-muted">
                        No documents uploaded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<div id="pdfPreviewModal" style="
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15,23,42,0.85);
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 16px;
    color: #fff;
    font-size: 15px;
    font-family: sans-serif;
">
    <div style="text-align: center; background: #1e293b; padding: 30px 40px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); display: flex; flex-direction: column; align-items: center; gap: 15px;">
        <i class="fas fa-spinner fa-spin" style="font-size:40px; color:#60a5fa;"></i>
        <div style="font-weight: 600;" id="pdfPreviewLoadingText">Preparing PDF...</div>
        <button id="pdfPreviewClose" style="margin-top: 10px; background: #ef4444; border: none; border-radius: 6px; color: white; padding: 6px 16px; cursor: pointer; font-size: 13px; font-weight: 500; transition: background 0.2s;">
            Cancel
        </button>
    </div>
</div>

@include('Dean_OSA.tracer.partials.printing')
@endsection
