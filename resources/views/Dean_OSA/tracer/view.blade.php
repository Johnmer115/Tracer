@extends('Dean_OSA.layouts.layout')

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

        $isReschedulingActive = filled($activity->reschedule_requested_at) && $activity->reschedule_status !== 'approved';
        $isReschedulingDone = filled($activity->reschedule_requested_at) && $activity->reschedule_status === 'approved';

        $pipeline = [
            [
                'label' => 'For Approval',
                'active' => $activity->status === 'for approval',
                'done' => in_array($activity->status, ['for approval finance', 'approved', 'completed']),
            ],
            [
                'label' => 'Finance Review',
                'active' => $activity->status === 'for approval finance',
                'done' => in_array($activity->status, ['approved', 'completed']),
            ],
            [
                'label' => 'Approved',
                'active' => $activity->status === 'approved' && !$isReschedulingActive && !$isReschedulingDone,
                'done' => $activity->status === 'completed' || $isReschedulingActive || $isReschedulingDone,
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
                <a href="{{ route('dean_osa.tracer.index') }}" class="btn btn-filter no-pdf">
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

            <div style="margin-bottom:28px;">
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
                                            @if($approvedBudget !== null)
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
                <div class="pdf-page-break html2pdf__page-break" aria-hidden="true"></div>
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
                                                @if($approvedBudget !== null)
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
                        <div class="print-reschedule" style="margin-bottom:28px;">
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
                                @if(filled($activity->reschedule_remarks))
                                    <div style="margin-top:12px;padding:8px 12px;background:#fff;border-left:3px solid var(--primary);border:1px solid var(--border);border-left-width:3px;border-radius:6px;font-size:12px;color:#374151;">
                                        <strong>Remarks:</strong> {{ $activity->reschedule_remarks }}
                                    </div>
                                @endif
                            </div>
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
                                <a href="{{ route('dean_osa.sarf-documents.show', $doc) }}"
                                    target="_blank" class="abtn abtn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $doc, 'download' => 1]) }}"
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

<style>
    .print-only {
        display: none;
    }

    .tracer-print-btn {
        background: #014ea8 !important;
        border-color: #013f88 !important;
        color: #fff !important;
        font-weight: 800 !important;
        box-shadow: 0 8px 16px rgba(1, 78, 168, 0.24) !important;
    }

    .tracer-print-btn:hover {
        background: #013f88 !important;
        transform: translateY(-1px);
    }

    /* Screen Styles */
    .pipeline-tracker {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow-x: auto;
    }

    .pipeline-step {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 80px;
    }

    .pipeline-step-inner {
        text-align: center;
        flex: 1;
    }

    .pipeline-connector {
        flex: 0 0 14px;
        height: 2px;
        border-radius: 2px;
    }

    .approval-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 14px;
        margin-bottom: 8px;
        border-radius: 8px;
    }

    .approval-card.status-approved {
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .approval-card.status-for-signature {
        background: #dbeafe;
        border: 1px solid #93c5fd;
    }

    .approval-card.status-disapproved {
        background: #fef2f2;
        border: 1px solid #fca5a5;
    }

    .approval-card.status-pending {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    /* PDF Export Styles */
    .tracer-pdf-export {
        width: 920px;
        max-width: 920px;
        min-width: 0;
        padding: 0;
        overflow: hidden;
        background: #fff;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        box-sizing: border-box;
    }

    .tracer-pdf-export,
    .tracer-pdf-export * {
        box-shadow: none !important;
        box-sizing: border-box;
        overflow-wrap: anywhere;
    }

    .tracer-pdf-export .print-only {
        display: block !important;
    }

    .tracer-pdf-export .print-inline {
        display: inline !important;
    }

    .tracer-pdf-export .print-title-code {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        padding: 0 0 12px !important;
        margin: 0 0 12px !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .tracer-pdf-export .print-second-page {
        page-break-before: always !important;
        break-before: page !important;
        padding-top: 0 !important;
        margin-top: -20px !important;
    }

    .tracer-pdf-export .print-second-page .approval-group {
        margin-bottom: 8px !important;
    }

    .tracer-pdf-export .print-second-page .approval-group-title {
        margin-bottom: 4px !important;
    }

    .tracer-pdf-export .print-details {
        padding: 14px 0 16px !important;
        background: #fff !important;
        border: 0 !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        border-radius: 0 !important;
        margin-bottom: 16px !important;
    }

    .tracer-pdf-export .print-details > div[style*="display:grid"] {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 12px 22px !important;
    }

    .tracer-pdf-export .pipeline-tracker {
        overflow: hidden !important;
        overflow-x: hidden !important;
        white-space: normal !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        display: flex !important;
        background: #fff !important;
        border: 0 !important;
        border-top: 1px solid #e5e7eb !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        padding: 10px 60px 10px 0px!important;
        margin-bottom: 18px !important;
    }

    .tracer-pdf-export .pipeline-step {
        width: auto !important;
        max-width: 100% !important;
    }

    .tracer-pdf-export .pipeline-step-inner {

        max-width: 100% !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .pipeline-step:first-child .pipeline-step-inner {
        padding-left: 0 !important;
    }

    .tracer-pdf-export .pipeline-step:last-child .pipeline-step-inner {
        padding-right: 0 !important;
    }

    .tracer-pdf-export .pipeline-step:last-child .pipeline-step-inner > div:last-child {
        font-size: 8px !important;
        white-space: normal !important;
        overflow-wrap: normal !important;
    }

    .tracer-pdf-export .pipeline-connector {
        flex: 0 0 8px !important;
        width: 8px !important;
        min-width: 8px !important;
        height: 2px !important;
    }

    .tracer-pdf-export .approval-group {
        page-break-inside: avoid;
        break-inside: avoid;
        margin-bottom: 16px !important;
    }

    .tracer-pdf-export .approval-track {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        margin-bottom: 4px !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .approval-line,
    .tracer-pdf-export .approval-dot {
        display: none !important;
    }

    .tracer-pdf-export .approval-card {
        align-items: flex-start !important;
        width: 100% !important;
        max-width: 100% !important;
        background: #fff !important;
        border: 0 !important;
        border-bottom: 1px dashed #d7dee8 !important;
        border-radius: 0 !important;
        margin-bottom: 2px !important;
        padding: 5px 0 !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .print-second-page .approval-card {
        margin-bottom: 0 !important;
        padding: 4px 0 !important;
    }

    .tracer-pdf-export .print-second-page .approval-card div[style*="margin-top:8px"] {
        margin-top: 1px !important;
    }

    .tracer-pdf-export .approval-card span[style*="padding:4px 8px"] {
        background: #fff !important;
        padding: 0 !important;
    }

    .tracer-pdf-export .print-reschedule {
        margin-bottom: 0 !important;
    }

    .tracer-pdf-export .print-reschedule > div:first-child {
        margin-bottom: 6px !important;
    }

    .tracer-pdf-export .print-reschedule > div[style*="background:#f9fafb"] {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        background: #fff !important;
        border: 0 !important;
        border-top: 1px solid #d7dee8 !important;
        border-bottom: 1px solid #d7dee8 !important;
        border-radius: 0 !important;
        padding: 7px 0 !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .print-reschedule div[style*="display:grid"] {
        gap: 8px 18px !important;
    }

    .tracer-pdf-export .pdf-page-break {
        display: block !important;
        height: 0 !important;
        page-break-after: always;
        break-after: page;
        padding-top: 0 !important;
    }

    .tracer-pdf-export .no-pdf,
    .tracer-pdf-export .no-print {
        display: none !important;
    }
</style>

<style media="print">
    /* Hides everything except the print content */
    nav, 
    .sidebar, 
    .navbar, 
    .panel-controls, 
    .btn, 
    .abtn,
    .panel-header,
    .app-header,
    header,
    aside,
    footer:not(.print-footer),
    .no-print {
        display: none !important;
    }

    /* Unset positioning of layout wrappers to allow viewport-relative position: fixed for print header/footer */
    body,
    .main,
    .content,
    .panel,
    main {
        position: static !important;
        transform: none !important;
        filter: none !important;
        perspective: none !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: none !important;
    }

    .panel {
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Content wrapper padding to clear fixed header/footer */
    .print-padding {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 0 !important;
        padding-right: 0.8in !important;
        padding-bottom: 0 !important;
        padding-left: 0.8in !important;
        overflow: hidden !important;
    }

    #tracerPdfContent,
    #tracerPdfContent * {
        box-sizing: border-box !important;
        max-width: 100% !important;
    }

    #tracerPdfContent {
        width: 100% !important;
        overflow: hidden !important;
    }

    .print-title-code {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        padding: 0 0 10px !important;
        margin: 0 0 12px !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .print-inline {
        display: inline !important;
    }

    /* Clean and minimal styles for Activity Details section only */
    .print-details {
        background: none !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
        margin-bottom: 15px !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }

    .print-details > div[style*="display:grid"] {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }

    /* Rescheduling section — allow page break before so it starts cleanly on a new page */
    .print-reschedule {
        page-break-before: auto;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }

    .print-second-page {
        page-break-before: always !important;
        break-before: page !important;
        padding-top: 0 !important;
    }

    .pdf-page-break {
        display: block !important;
        height: 0 !important;
        page-break-after: always !important;
        break-after: page !important;
    }

    .print-reschedule > div[style*="background:#f9fafb"] {
        background: none !important;
        border: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
    }

    /* Clean and minimal styles for pipeline tracker */
    .pipeline-tracker {
        width: 100% !important;
        max-width: 100% !important;
        background: none !important;
        border: none !important;
        padding: 5px 45px !important;
        margin-bottom: 15px !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
    }

    .pipeline-step {
        min-width: 0 !important;
        width: auto !important;
        max-width: 100% !important;
    }

    /* Clean and minimal styles for signatories (remove heavy borders and backgrounds) */
    .approval-group {
        margin-bottom: 15px !important;
    }

    /* Force Finance Approval group to start on a new page */
    .print-finance-group {
        page-break-before: auto !important;
        break-before: auto !important;
        padding-top: 0 !important;
    }

    /* Reset timeline container indentation */
    .approval-track {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        margin-bottom: 10px !important;
        overflow: hidden !important;
    }

    /* Hide the timeline vertical line and circular dot to clean up space and avoid alignment issues */
    .approval-line,
    .approval-dot {
        display: none !important;
    }

    /* Clean signatory row cards */
    .approval-card {
        width: 100% !important;
        max-width: 100% !important;
        background: none !important;
        border: none !important;
        border-bottom: 1px dashed #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 6px 0 !important;
        margin-bottom: 4px !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }

    /* Font size reductions for printing */
    .approval-group div[style*="font-size:13px"] {
        font-size: 11px !important;
    }

    .approval-group div[style*="display:flex;gap:8px;flex-wrap:wrap"] {
        margin-top: 2px !important;
        gap: 12px !important;
    }

    .approval-group span[style*="padding:4px 8px"] {
        padding: 0 !important;
        background: none !important;
        font-size: 10px !important;
    }

    /* Page margins — top margin tall enough for fixed header, bottom for fixed footer */
    @page {
        size: letter portrait;
        margin-top: 1.0in;
        margin-right: 0.8in;
        margin-bottom: 0.9in;
        margin-left: 0.8in;

        @bottom-right {
            content: "Page " counter(page);
            font-size: 10px;
        }
    }

    /* Display print-only elements */
    .print-only {
        display: block !important;
    }

    /* Fixed header — repeats on every printed page */
    .print-header {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        z-index: 1000;
        padding-bottom: 5px;
    }

    /* Fixed footer — repeats on every printed page */
    .print-footer {
        position: fixed !important;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        z-index: 1000;
        padding-top: 5px;
    }

    /* Page number via CSS counter(page) */
    .print-footer-right::after {
        content: "";
    }

    /* Prevent breaking inside specific sections */
    .view-section,
    .approved-upload-card,
    .approval-group,
    .print-reschedule {
        page-break-inside: avoid;
    }
</style>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('downloadTracerPdf');
            const content = document.getElementById('tracerPdfContent');
            const modal = document.getElementById('pdfPreviewModal');
            const closeBtn = document.getElementById('pdfPreviewClose');
            const loadingText = document.getElementById('pdfPreviewLoadingText');

            if (!button || !content || !modal) {
                return;
            }

            let logoPromise = null;
            let fontPromise = null;
            let cachedPdfBlob = null;

            const toDataUrl = (src) => new Promise((resolve) => {
                const image = new Image();
                image.crossOrigin = 'anonymous';
                image.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = image.naturalWidth;
                    canvas.height = image.naturalHeight;
                    const context = canvas.getContext('2d');
                    context.drawImage(image, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                };
                image.onerror = () => resolve(null);
                image.src = src;
            });

            const getLogos = () => {
                if (!logoPromise) {
                    logoPromise = Promise.all([
                        toDataUrl("{{ asset('image/logo/arellano_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/au_osa_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/osa_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/globe.logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/gmail_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/call_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/fb_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/insta_logo.png') }}"),
                    ]);
                }
                return logoPromise;
            };

            const toBase64Font = async (url, name, style) => {
                try {
                    const res = await fetch(url);
                    if (!res.ok) return null;
                    const buffer = await res.arrayBuffer();
                    const bytes = new Uint8Array(buffer);
                    let binary = '';
                    const len = bytes.byteLength;
                    for (let i = 0; i < len; i++) {
                        binary += String.fromCharCode(bytes[i]);
                    }
                    return { name, style, base64: btoa(binary) };
                } catch (e) {
                    console.error('Failed to load font:', url, e);
                    return null;
                }
            };

            const getFonts = () => {
                if (!fontPromise) {
                    fontPromise = Promise.all([
                        toBase64Font('https://fonts.gstatic.com/s/ptsansnarrow/v17/jx5YrD424m5X1n4B3Xb3w00.ttf', 'PTSansNarrow', 'normal'),
                        toBase64Font('https://fonts.gstatic.com/s/ptsansnarrow/v17/jx5VD424m5X1n4B3Xb3w1067t8T.ttf', 'PTSansNarrow', 'bold')
                    ]);
                }
                return fontPromise;
            };

            const addPageChrome = (pdf, logos, fonts) => {
                const pageCount = pdf.internal.getNumberOfPages();
                const width = pdf.internal.pageSize.getWidth();
                const height = pdf.internal.pageSize.getHeight();
                const printInset = 20.32;
                const centerX = width / 2;

                const hasNarrow = fonts && fonts.some(f => f && f.name === 'PTSansNarrow');
                const fontName = hasNarrow ? 'PTSansNarrow' : 'helvetica';

                for (let page = 1; page <= pageCount; page += 1) {
                    pdf.setPage(page);
                    
                    // Double underline matching reference
                    pdf.setDrawColor(17, 24, 39);
                    pdf.setLineWidth(0.65);
                    pdf.line(printInset, 19.5, width - printInset, 19.5);
                    pdf.setLineWidth(0.2);
                    pdf.line(printInset, 20.4, width - printInset, 20.4);

                    // Set font details to measure the width of the main title
                    pdf.setFont(fontName, 'bold');
                    const titleFontSize = hasNarrow ? 14 : 11.5;
                    pdf.setFontSize(titleFontSize);
                    
                    const titleText = 'ARELLANO UNIVERSITY';
                    const titleWidth = pdf.getTextWidth(titleText);
                    const gap = 3.5; // gap between logo and text
                    const logoSize = 13.5; // size of the circular logos

                    const leftLogoX = centerX - (titleWidth / 2) - gap - logoSize;
                    const rightLogoX = centerX + (titleWidth / 2) + gap;

                    // Left Logo
                    if (logos.arellano) {
                        pdf.addImage(logos.arellano, 'PNG', leftLogoX, 3.8, logoSize, logoSize);
                    }

                    // Right Logo
                    if (logos.auOsa) {
                        pdf.addImage(logos.auOsa, 'PNG', rightLogoX, 3.8, logoSize, logoSize);
                    }

                    // Center-aligned text block
                    pdf.text(titleText, centerX, 8.2, { align: 'center' });
                    
                    pdf.setFontSize(hasNarrow ? 11 : 9.5);
                    pdf.text('OFFICE FOR STUDENT AFFAIRS', centerX, 12.2, { align: 'center' });
                    
                    pdf.setFont(fontName, 'normal');
                    pdf.setFontSize(hasNarrow ? 9.5 : 8);
                    pdf.text('2600 Legarda Street, Sampaloc, Manila', centerX, 15.8, { align: 'center' });

                    // Footer layout matching the print reference
                    const footerTopY = height - 16.5;
                    const footerBaseY = height - 7.2;
                    const footerLogoX = printInset + 36;
                    const footerContactX = printInset + 70;
                    const footerRightX = width - printInset;

                    // Double underline above contact information
                    const lineStartX = footerContactX;
                    pdf.setDrawColor(17, 24, 39);
                    pdf.setLineWidth(0.65);
                    pdf.line(lineStartX, footerTopY, footerRightX, footerTopY);
                    pdf.setLineWidth(0.2);
                    pdf.line(lineStartX, footerTopY + 1.1, footerRightX, footerTopY + 1.1);

                    // #oneArellano on the left
                    pdf.setFont(fontName, 'bold');
                    pdf.setFontSize(hasNarrow ? 14 : 11.5);
                    pdf.setTextColor(0, 0, 0);
                    pdf.text('#oneArellano', printInset, footerBaseY);

                    // OSA Logo beside #oneArellano (circular/square 1:1 aspect ratio)
                    if (logos.osa) {
                        pdf.addImage(logos.osa, 'PNG', footerLogoX, height - 17.5, 12.5, 12.5);
                    }

                    // Contact Info Details
                    pdf.setFont(fontName, 'normal');
                    pdf.setFontSize(hasNarrow ? 8.5 : 7.2);
                    pdf.setTextColor(0, 0, 0);

                    const iconSize = 3.6;
                    const row1Y = height - 11.2;
                    const row2Y = height - 7.2;
                    const col1X = footerContactX;
                    const col2X = footerContactX + 54;

                    if (logos.globe) {
                        pdf.addImage(logos.globe, 'PNG', col1X, row1Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('www.arellano.edu.ph', col1X + 5, row1Y);

                    if (logos.call) {
                        pdf.addImage(logos.call, 'PNG', col1X, row2Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('(02) 8 734 7371 to 75 loc. 206', col1X + 5, row2Y);

                    if (logos.gmail) {
                        pdf.addImage(logos.gmail, 'PNG', col2X, row1Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('main.osa@arellano.edu.ph', col2X + 5, row1Y);

                    // Use the fb and insta image logos
                    if (logos.fb) {
                        pdf.addImage(logos.fb, 'PNG', col2X, row2Y - 3.1, iconSize, iconSize);
                    }
                    if (logos.insta) {
                        pdf.addImage(logos.insta, 'PNG', col2X + 4.5, row2Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('ArellanoUniversityOSA', col2X + 9.5, row2Y);

                    // Page number at the very bottom right
                    pdf.setFontSize(hasNarrow ? 7.5 : 6);
                    pdf.text(`Page ${page} of ${pageCount}`, width - printInset, height - 3, { align: 'right' });
                }
            };

            const createExportNode = () => {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'absolute';
                wrapper.style.left = '0';
                wrapper.style.top = '-9999px';
                wrapper.style.width = '920px';
                wrapper.style.maxWidth = '920px';
                wrapper.style.overflow = 'hidden';
                wrapper.style.zIndex = '-9999';
                wrapper.style.background = '#fff';

                const clone = content.cloneNode(true);
                clone.removeAttribute('id');
                clone.classList.add('tracer-pdf-export');
                clone.style.width = '920px';
                clone.style.maxWidth = '920px';
                clone.style.minWidth = '0';
                clone.style.overflow = 'hidden';
                clone.style.overflowX = 'hidden';
                clone.style.boxSizing = 'border-box';
                clone.style.padding = '0';
                clone.style.background = '#fff';

                wrapper.appendChild(clone);
                document.body.appendChild(wrapper);

                // Force layout recalculation so html2canvas measures correctly
                void wrapper.offsetWidth;
                void wrapper.offsetHeight;

                return { wrapper, clone };
            };

            const createPdfPageNode = (clone, selectors) => {
                const page = document.createElement('div');
                page.className = 'tracer-pdf-export tracer-pdf-page';
                page.style.width = '920px';
                page.style.maxWidth = '920px';
                page.style.minWidth = '0';
                page.style.overflow = 'hidden';
                page.style.boxSizing = 'border-box';
                page.style.padding = '0';
                page.style.margin = '0';
                page.style.background = '#fff';

                selectors.forEach((selector) => {
                    const node = clone.querySelector(selector);
                    if (node) {
                        const copy = node.cloneNode(true);
                        copy.style.marginTop = '0';
                        page.appendChild(copy);
                    }
                });

                return page.childElementCount ? page : null;
            };

            const renderPageToCanvas = (page) => html2canvas(page, {
                scale: 1.5,
                useCORS: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                windowWidth: 920,
                width: 920,
                x: 0,
                y: 0,
                ignoreElements: (el) => el.classList.contains('no-pdf') || el.classList.contains('no-print'),
            });

            const generatePdf = async (onProgress) => {
                if (cachedPdfBlob) {
                    return cachedPdfBlob;
                }

                if (onProgress) onProgress('Creating export node...');
                const { wrapper, clone } = createExportNode();

                if (onProgress) onProgress('Waiting for layout to settle...');
                await new Promise(resolve => setTimeout(resolve, 300));

                if (onProgress) onProgress('Loading logos...');
                const logos = await getLogos();

                if (onProgress) onProgress('Loading fonts...');
                const fonts = await getFonts();

                if (onProgress) onProgress('Rendering PDF...');
                const options = {
                    margin: [22, 0, 17, 20.32],
                    filename: button.dataset.filename || 'sarf-tracer.pdf',
                    image: { type: 'jpeg', quality: 0.92 },
                    html2canvas: {
                        scale: 1.5,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        scrollX: -window.scrollX,
                        scrollY: -window.scrollY,
                        windowWidth: 920,
                        width: 920,
                        x: 0,
                        y: 0,
                        ignoreElements: (el) => el.classList.contains('no-pdf') || el.classList.contains('no-print'),
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'letter',
                        orientation: 'portrait',
                        compress: true,
                    },
                    pagebreak: {
                        mode: ['css', 'legacy'],
                        before: ['.print-second-page', '.html2pdf__page-break'],
                        avoid: ['.approval-card', '.approval-group-title', '.print-details', '.print-reschedule'],
                    },
                };

                return new Promise((resolve, reject) => {
                    html2pdf()
                        .set(options)
                        .from(clone)
                        .toPdf()
                        .get('pdf')
                        .then((pdf) => {
                            if (onProgress) onProgress('Adding headers and footers...');
                            
                            if (fonts) {
                                fonts.forEach(f => {
                                    if (f && f.base64) {
                                        const filename = `${f.name}-${f.style}.ttf`;
                                        pdf.addFileToVFS(filename, f.base64);
                                        pdf.addFont(filename, f.name, f.style);
                                    }
                                });
                            }

                            addPageChrome(pdf, {
                                arellano: logos[0],
                                auOsa: logos[1],
                                osa: logos[2],
                                globe: logos[3],
                                gmail: logos[4],
                                call: logos[5],
                                fb: logos[6],
                                insta: logos[7],
                            }, fonts);
                            const blob = pdf.output('blob');
                            cachedPdfBlob = blob;
                            wrapper.remove();
                            resolve(blob);
                        })
                        .catch((err) => {
                            wrapper.remove();
                            reject(err);
                        });
                });
            };

            const openPreview = async () => {
                if (typeof html2pdf === 'undefined') {
                    window.print();
                    return;
                }

                modal.style.display = 'flex';
                loadingText.textContent = 'Generating PDF...';

                try {
                    const blob = await generatePdf((message) => {
                        loadingText.textContent = message;
                    });

                    const blobUrl = URL.createObjectURL(blob);
                    
                    // Close the modal cleanly
                    closeModal();
                    
                    // Open the PDF blob in a new browser window/tab
                    window.open(blobUrl, '_blank');
                } catch (error) {
                    console.error('PDF generation failed:', error);
                    loadingText.textContent = 'Failed to generate PDF';
                    setTimeout(closeModal, 1500);
                }
            };

            const closeModal = () => {
                modal.style.display = 'none';
                cachedPdfBlob = null;
            };

            button.addEventListener('click', openPreview);
            closeBtn.addEventListener('click', closeModal);

            // Close modal on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>
@endpush
@endsection
