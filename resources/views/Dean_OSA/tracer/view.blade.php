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
        $mainSignatories = collect($mainSignatories)
            ->filter(fn($sig) => $sig['field'] !== 'approval_dir_basic_ed' || $needsBasicEd)
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
    <div class="print-only print-header" style="position: fixed; top: 0; left: 0; right: 0; width: 100%; background: #fff; z-index: 1000;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <!-- LOGO HERE -->
                <div class="print-logo">
                    <img src="{{ asset('image/logo/arellano_logo.png') }}" alt="Arellano University Logo" style="width: 50px; height: auto;">
                </div>
                <!-- /LOGO HERE -->
                <div>
                    <!-- SCHOOL NAME -->
                    <div class="print-school-name" style="font-size: 14px; font-weight: 700; text-transform: uppercase;">
                        Arellano University
                    </div>
                    <!-- /SCHOOL NAME -->
                    <div class="print-doc-title" style="font-size: 16px; font-weight: 800; color: #014ea8;">
                        {{ $activity->title }}
                    </div>
                </div>
            </div>
            <div style="text-align: right; font-size: 12px;">
                <strong>Code:</strong> {{ $activity->code }}<br>
                <strong>Activity:</strong> {{ $activity->title }}
            </div>
        </div>
    </div>
    <!-- /PRINT HEADER PLACEHOLDER -->

    <!-- PRINT FOOTER PLACEHOLDER -->
    <div class="print-only print-footer" style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; background: #fff; z-index: 1000;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #000; padding-top: 5px; font-size: 10px; width: 100%;">
            <!-- FOOTER LEFT -->
            <div class="print-footer-left">
                Footer Left Placeholder
            </div>
            <!-- /FOOTER LEFT -->

            <!-- FOOTER CENTER -->
            <div class="print-footer-center">
                Footer Center Placeholder
            </div>
            <!-- /FOOTER CENTER -->

            <!-- FOOTER RIGHT -->
            <div class="print-footer-right"></div>
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
                <button onclick="window.print()" class="btn btn-filter" style="cursor: pointer; margin-right: 5px;">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('dean_osa.tracer.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;" class="print-padding">
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
                <div style="display:flex;align-items:center;margin-bottom:20px;padding:12px 16px;background:#f8fafc;border:1px solid var(--border);border-radius:10px;overflow-x:auto;">
                    @foreach($pipeline as $pi => $ps)
                        @php $done = $ps['done']; $active = $ps['active']; @endphp
                        <div style="display:flex;align-items:center;flex:1;min-width:80px;">
                            <div style="text-align:center;flex:1;">
                                <div style="width:26px;height:26px;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;
                                    background:{{ $done ? '#dcfce7' : ($active ? 'var(--primary)' : '#e2e8f0') }};
                                    color:{{ $done ? '#16a34a' : ($active ? '#fff' : '#94a3b8') }};
                                    font-size:11px;box-shadow:{{ $active ? '0 0 0 3px rgba(1,78,168,.15)' : 'none' }};">
                                    <i class="fas {{ $done ? 'fa-check' : 'fa-circle' }}"></i>
                                </div>
                                <div style="font-size:9px;font-weight:{{ $active ? 700 : 500 }};color:{{ $done ? '#16a34a' : ($active ? 'var(--primary)' : '#94a3b8') }};">
                                    {{ $ps['label'] }}
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div style="flex:0 0 14px;height:2px;border-radius:2px;background:{{ $done ? '#86efac' : '#e2e8f0' }};"></div>
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
                        <div style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                            <div style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                            @foreach($mainSignatories as $sig)
                                @php
                                    [$icon, $color, $bg, $border, $label] = $approvalIcon($activity->{$sig['field']} ?? 'pending');
                                    $approvedAt = $activity->{$sig['time']} ?? null;
                                    $approvedBudget = $activity->{$sig['budget']} ?? null;
                                @endphp
                                <div style="position:relative;display:flex;align-items:flex-start;gap:12px;padding:10px 14px;margin-bottom:8px;background:{{ $bg }};border:1px solid {{ $border }};border-radius:8px;">
                                    <div style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
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

            @if(!$financeSignatories->isEmpty() || filled($activity->reschedule_requested_at))
                <div class="print-second-page">
                    @unless($financeSignatories->isEmpty())
                        <div class="approval-group print-finance-group" style="margin-bottom:24px;">
                            <div class="approval-group-title" style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                                Finance Approval
                            </div>
                            <div style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:8px;">
                                <div style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                                @foreach($financeSignatories as $sig)
                                    @php
                                        [$icon, $color, $bg, $border, $label] = $approvalIcon($activity->{$sig['field']} ?? 'pending');
                                        $approvedAt = $activity->{$sig['time']} ?? null;
                                        $approvedBudget = $activity->{$sig['budget']} ?? null;
                                    @endphp
                                    <div style="position:relative;display:flex;align-items:flex-start;gap:12px;padding:10px 14px;margin-bottom:8px;background:{{ $bg }};border:1px solid {{ $border }};border-radius:8px;">
                                        <div style="position:absolute;left:-22px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $border }};"></div>
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
                                <i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Reschedule Request
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

<style>
    .print-only {
        display: none;
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
        padding: 0 !important;
        padding-top: 0 !important;
    }

    /* Clean and minimal styles for Activity Details section only */
    .print-details {
        background: none !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
        margin-bottom: 15px !important;
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
        padding-top: 0.75in !important;
    }

    .print-reschedule > div[style*="background:#f9fafb"] {
        background: none !important;
        border: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
    }

    /* Clean and minimal styles for pipeline tracker */
    div[style*="overflow-x:auto"] {
        background: none !important;
        border: none !important;
        padding: 5px 0 !important;
        margin-bottom: 15px !important;
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
    .approval-group div[style*="padding-left:28px"] {
        padding-left: 0 !important;
        margin-bottom: 10px !important;
    }

    /* Hide the timeline vertical line and circular dot to clean up space and avoid alignment issues */
    .approval-group div[style*="position:absolute"] {
        display: none !important;
    }

    /* Clean signatory row cards */
    .approval-group div[style*="background"] {
        background: none !important;
        border: none !important;
        border-bottom: 1px dashed #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 6px 0 !important;
        margin-bottom: 4px !important;
        box-shadow: none !important;
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
        size: 8.5in 13in     portrait;
        margin-top: 1.2in;
        margin-right: 0.8in;
        margin-bottom: 0.7in;
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
@endsection
