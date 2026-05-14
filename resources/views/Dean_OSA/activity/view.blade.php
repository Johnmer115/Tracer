@extends('Dean_OSA.layouts.layout')

@section('title', 'View Activity | SARF Tracking')
@section('page-title', 'View Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <style>
    /* ── Show-page specific styles ──────────────────────── */
    .show-section {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .show-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        font-size: 13.5px;
        font-weight: 700;
        color: #1e293b;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }
    .show-section-header i { color: #3b82f6; font-size: 14px; }
    .show-section-header.purple { background: #faf5ff; }
    .show-section-header.purple i { color: #8b5cf6; }
    .show-section-header.green  { background: #f0fdf4; }
    .show-section-header.green  i { color: #16a34a; }
    .show-section-header.amber  { background: #fffbeb; }
    .show-section-header.amber  i { color: #d97706; }

    .show-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        padding: 0;
    }
    .show-field {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .show-field:nth-child(odd)  { border-right: 1px solid #f1f5f9; }
    .show-field.full            { grid-column: 1 / -1; border-right: none; }
    .show-field:last-child,
    .show-field:nth-last-child(2):nth-child(odd) { border-bottom: none; }
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
    .tag-display {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 2px;
    }
    .tag-display .tag {
        background: #dbeafe; color: #1d4ed8;
        border-radius: 20px; padding: 3px 10px;
        font-size: 12.5px; font-weight: 600;
        cursor: default;
    }
    .tag-display .tag.green  { background: #dcfce7; color: #15803d; }
    .tag-display .tag.purple { background: #ede9fe; color: #6d28d9; }
    .inline-tag {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
        border-radius: 5px;
        padding: 2px 8px;
        margin-left: 8px;
        vertical-align: middle;
    }
    .amount-green { font-weight: 700; color: #16a34a; font-size: 15px; }
    .amount-amber { font-weight: 700; color: #d97706; font-size: 15px; }

    /* Objectives */
    .obj-display {
        list-style: none;
        margin: 4px 0 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .obj-display li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 13.5px;
        color: #1e293b;
    }
    .obj-display li::before {
        content: '';
        flex-shrink: 0;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #3b82f6;
        margin-top: 6px;
    }

    /* Status badge */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: capitalize;
    }
    .status-pill.pending      { background: #fef3c7; color: #92400e; }
    .status-pill.approved     { background: #dcfce7; color: #15803d; }
    .status-pill.for-revision { background: #dbeafe; color: #1d4ed8; }
    .status-pill.rejected     { background: #fee2e2; color: #991b1b; }

    /* Attachment list */
    .attachment-view-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .12s;
    }
    .attachment-view-row:last-child { border-bottom: none; }
    .attachment-view-row:hover { background: #f8fafc; }
    .attachment-view-left {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        color: #1e293b;
    }
    .attachment-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #3b82f6;
        background: #dbeafe;
        border-radius: 20px;
        padding: 4px 12px;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .attachment-view-btn:hover { background: #bfdbfe; color: #1d4ed8; }
    .no-attachment { color: #94a3b8; font-style: italic; font-size: 13px; }

    /* Late submission notice */
    .late-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fef9c3;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: 13.5px;
        color: #78350f;
    }
    .late-notice i { color: #f59e0b; font-size: 18px; margin-top: 1px; }

    /* Action bar at bottom */
    .show-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
        padding: 16px 20px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .show-actions-right { display: flex; gap: 10px; flex-wrap: wrap; }

    @media (max-width: 640px) {
        .show-grid { grid-template-columns: 1fr; }
        .show-field.full,
        .show-field:nth-child(odd) { border-right: none; }
    }
    </style>
@endpush

@section('content')
<section class="panel" style="padding: 25px;">

    {{-- ── Success / Info flash ── --}}
    @if(session('success'))
        <div class="alert alert-success"><b>{{ session('success') }}</b></div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-alt"></i> SARF Request
            </div>
            <div class="panel-controls">
                {{-- SARF code --}}
                <div class="sarf-code-display sarf-code-display--header">
                    <span class="code-label">SARF Code</span>
                    <i class="fas fa-hashtag" style="color:#93c5fd; font-size:12px;"></i>
                    <span>{{ $activity->code }}</span>
                </div>
                {{-- Status pill --}}
                @php
                    $pillClass = match($activity->status) {
                        'approved'      => 'approved',
                        'for revision'  => 'for-revision',
                        'rejected'      => 'rejected',
                        default         => 'pending',
                    };
                @endphp
                <span class="status-pill {{ $pillClass }}">
                    <i class="fas fa-circle" style="font-size:7px;"></i>
                    {{ ucfirst($activity->status) }}
                </span>
                <a href="{{ route('dean_osa.activity.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">

            {{-- ══════════════════════════════════════════════
                 SECTION 1 — Organizational Context
            ══════════════════════════════════════════════ --}}
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
                        @php
                            $levels = is_array($activity->level)
                                ? $activity->level
                                : (filled($activity->level) ? [$activity->level] : []);
                        @endphp
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
                        @php
                            $depts = is_array($activity->department)
                                ? $activity->department
                                : (filled($activity->department) ? [$activity->department] : []);
                        @endphp
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

            {{-- ══════════════════════════════════════════════
                 SECTION 2 — Activity Information
            ══════════════════════════════════════════════ --}}
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
                        @php
                            $objs = is_array($activity->objectives)
                                ? $activity->objectives
                                : (filled($activity->objectives) ? [$activity->objectives] : []);
                        @endphp
                        @if(count($objs))
                            <ul class="obj-display">
                                @foreach($objs as $obj)
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

            {{-- ══════════════════════════════════════════════
                 SECTION 3 — Schedule, Conduct & Extras
            ══════════════════════════════════════════════ --}}
            <div class="show-section">
                <div class="show-section-header green">
                    <i class="fas fa-clock"></i> Schedule, Conduct & Extras
                </div>
                <div class="show-grid">

                    <div class="show-field">
                        <div class="show-label">Date of Activity</div>
                        <div class="show-value">
                            {{ $activity->date_of_activity
                                ? \Carbon\Carbon::parse($activity->date_of_activity)->format('F d, Y')
                                : '—' }}
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
                                @if($activity->venue_type ?? null)
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
                        <div class="show-value">{{ $activity->participants_count ? number_format($activity->participants_count) : '—' }}</div>
                    </div>

                    <div class="show-field">
                        <div class="show-label">Participant Profile</div>
                        <div class="show-value">{{ $activity->participants_profile ?? '—' }}</div>
                    </div>

                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 SECTION 4 — Budgetary Requirements
            ══════════════════════════════════════════════ --}}
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

                    @if(in_array($activity->funds, ['With Budget','ATC']))
                        <div class="show-field">
                            <div class="show-label">Amount</div>
                            <div class="show-value amount-green">
                                {{ $activity->amount !== null ? '₱ ' . number_format($activity->amount, 2) : '—' }}
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

                    @if(in_array($activity->funds, ['With Budget','ATC']) || filled($activity->canteen) || filled($activity->procurement))
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

            {{-- ══════════════════════════════════════════════
                 SECTION 5 — Attachment Files
            ══════════════════════════════════════════════ --}}
            @php
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
            $docs = $activity->sarfDocuments->keyBy('type');
            @endphp

            <div class="show-section">
                <div class="show-section-header">
                    <i class="fas fa-paperclip"></i> Attachment Files
                    <span style="margin-left:auto; font-size:12px; font-weight:400; color:#64748b;">
                        {{ $docs->count() }} of {{ count($sarfLabels) }} types attached
                    </span>
                    @if($docs->isNotEmpty())
                        <a href="{{ route('dean_osa.sarf-documents.print-activity', $activity) }}"
                           target="_blank"
                           class="attachment-view-btn">
                            <i class="fas fa-print"></i> Print All
                        </a>
                    @endif
                </div>

                @if($docs->isEmpty())
                    <div style="padding:20px; text-align:center;">
                        <span class="no-attachment"><i class="fas fa-folder-open"></i> No attachments uploaded yet.</span>
                    </div>
                @else
                    @foreach($sarfLabels as $type => $label)
                        @if($docs->has($type))
                            <div class="attachment-view-row">
                                <div class="attachment-view-left">
                                    <span class="sarf-badge">{{ $type }}</span>
                                    <span>{{ $label }}</span>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="{{ route('dean_osa.sarf-documents.show', $docs[$type]) }}"
                                       target="_blank"
                                       class="attachment-view-btn">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- ── Action bar ── --}}
            <div class="show-actions">
                <div>
                    <span style="font-size:12px; color:#94a3b8;">
                        Last updated: {{ $activity->updated_at?->format('F d, Y h:i A') ?? '—' }}
                    </span>
                </div>
                <div class="show-actions-right">
                    <a href="{{ route('dean_osa.activity.index') }}" class="btn btn-filter">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

        </div>{{-- /padding --}}
    </div>{{-- /panel --}}
</section>
@endsection
