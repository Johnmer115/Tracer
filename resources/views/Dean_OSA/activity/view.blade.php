@extends($layout ?? 'Dean_OSA.layouts.layout')

@section('title', 'View Activity | SARF Tracking')
@section('page-title', 'View Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/activity-show.css') }}">

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
                @include('partials.sarf-status-badge', ['activity' => $activity])
                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.index') }}" class="btn btn-filter">
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
                        <div class="show-label">Department(s)</div>
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

                    <div class="show-field full">
                        <div class="show-label">Organization(s)</div>
                        @php
                            $orgs = is_array($activity->organizations)
                                ? $activity->organizations
                                : (filled($activity->organizations) ? [$activity->organizations] : []);
                        @endphp
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
                        <div class="show-value">{{ $activity->activityDateDisplay('F d, Y') ?? '—' }}</div>
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
                    <div class="show-field">
                        <div class="show-label">Public Poster</div>
                        <div class="show-value">{{ $activity->public_poster ?? '---' }}</div>
                    </div>

                    <div class="show-field">
                        <div class="show-label">Waiver / Consent / Legal Concern</div>
                        <div class="show-value">{{ $activity->waiver_consent ?? '---' }}</div>
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
            $customLabels = $docs
                ->keys()
                ->filter(fn ($type) => str_starts_with($type, 'OTHER:'))
                ->mapWithKeys(fn ($type) => [$type => substr($type, 6)]);
            $attachmentLabels = collect($sarfLabels)->merge($customLabels);
            $hasDigitalDocs = $docs->contains(fn ($doc) => filled($doc->file_path));
            @endphp

            <div class="show-section">
                <div class="show-section-header">
                    <i class="fas fa-paperclip"></i> Attachment Files
                    <span style="margin-left:auto; font-size:12px; font-weight:400; color:#64748b;">
                        {{ $docs->count() }} of {{ $attachmentLabels->count() }} types attached
                    </span>
                </div>

                @if($docs->isEmpty())
                    <div style="padding:20px; text-align:center;">
                        <span class="no-attachment"><i class="fas fa-folder-open"></i> No attachments uploaded yet.</span>
                    </div>
                @else
                    @foreach($attachmentLabels as $type => $label)
                        @if($docs->has($type))
                            <div class="attachment-view-row">
                                <div class="attachment-view-left">
                                    <span class="sarf-badge">{{ str_starts_with($type, 'OTHER:') ? 'OTH' : $type }}</span>
                                    <span>{{ $label }}</span>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    @if($docs[$type]->file_path)
                                        <a href="{{ route(($routePrefix ?? 'dean_osa') . '.sarf-documents.show', ['document' => $docs[$type], 'download' => 1]) }}"
                                           class="attachment-view-btn">
                                            <i class="fas fa-download"></i> Download File
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

            {{-- ── Action bar ── --}}
            <div class="show-actions">
                <div>
                    <span style="font-size:12px; color:#94a3b8;">
                        Last updated: {{ $activity->updated_at?->format('F d, Y h:i A') ?? '—' }}
                    </span>
                </div>
                <div class="show-actions-right">
                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.index') }}" class="btn btn-filter">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

        </div>{{-- /padding --}}
    </div>{{-- /panel --}}
</section>
@endsection
