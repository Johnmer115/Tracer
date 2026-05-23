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

        $pipeline = [
            ['label' => 'For Approval', 'val' => 'for approval'],
            ['label' => 'Finance Review', 'val' => 'for approval finance'],
            ['label' => 'Approved', 'val' => 'approved'],
            ['label' => 'Completed', 'val' => 'completed'],
        ];
        $pipeIdx = collect($pipeline)->search(fn($s) => $s['val'] === $activity->status);
        $pipeIdx = $pipeIdx === false ? -1 : $pipeIdx;

        $approvalIcon = fn($v) => match($v ?? 'pending') {
            'approved' => ['fas fa-check-circle', '#16a34a', '#dcfce7', '#86efac', 'Approved'],
            'for signature' => ['fas fa-pen-nib', '#014ea8', '#dbeafe', '#93c5fd', 'For Signature'],
            'disapproved' => ['fas fa-times-circle', '#dc2626', '#fef2f2', '#fca5a5', 'Disapproved'],
            default => ['fas fa-clock', '#64748b', '#f1f5f9', '#e2e8f0', 'Pending'],
        };
    @endphp

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
                <a href="{{ route('dean_osa.tracer.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">
            {{-- ===== Details first ===== --}}
            <div style="padding:16px;background:#f9fafb;border:1px solid var(--border);border-radius:10px;margin-bottom:20px;">
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
                        @php $done = $pi < $pipeIdx; $active = $pi === $pipeIdx; @endphp
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

                @foreach([
                    'For Approval' => $mainSignatories,
                    'Finance Approval' => $financeSignatories,
                ] as $groupTitle => $groupSignatories)
                    @continue($groupSignatories->isEmpty())
                    <div style="margin-bottom:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);padding-left:4px;">
                        {{ $groupTitle }}
                    </div>
                    <div style="display:flex;flex-direction:column;gap:0;position:relative;padding-left:28px;margin-bottom:24px;">
                        <div style="position:absolute;left:11px;top:8px;bottom:8px;width:2px;background:#e2e8f0;"></div>

                        @foreach($groupSignatories as $sig)
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
                @endforeach
            </div>

            {{-- ===== Documents last ===== --}}
            <div>
                <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151;">
                    <i class="fas fa-paperclip"></i> SARF Documents
                    @if($activity->sarfDocuments->isNotEmpty())
                        <a href="{{ route('dean_osa.sarf-documents.print-activity', $activity) }}"
                            target="_blank" class="abtn abtn-view" title="Print All" style="margin-left:8px;">
                            <i class="fas fa-print"></i>
                        </a>
                    @endif
                </div>
                @forelse($activity->sarfDocuments as $doc)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;margin-bottom:8px;border:1px solid var(--border);border-radius:8px;background:#fff;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge b-pending">{{ $doc->type }}</span>
                            <div>
                                <div style="font-weight:600;font-size:13px;">{{ $doc->original_filename }}</div>
                                <div class="td-muted" style="font-size:11px;">Uploaded {{ $doc->created_at?->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="{{ route('dean_osa.sarf-documents.show', $doc) }}"
                                target="_blank" class="abtn abtn-view" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $doc, 'download' => 1]) }}"
                                class="abtn abtn-edit" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
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
@endsection
