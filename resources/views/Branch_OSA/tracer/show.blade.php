@extends('Branch_OSA.layouts.layout')

@section('title', 'Activity Details | SARF Tracking')
@section('page-title', 'Activity Details')

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-route"></i> Activity Tracer</div>
            <div class="panel-controls">
                <div style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; color:#475569;">
                    <i class="fas fa-hashtag" style="color:#93c5fd; font-size:11px;"></i>
                    {{ $activity->code }}
                </div>
                <a href="{{ route('branch_osa.tracer.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">
            {{-- Activity Information --}}
            <div class="show-section" style="margin-bottom:16px;">
                <div class="show-section-header">
                    <i class="fas fa-calendar-alt"></i> Activity Information
                </div>
                <div style="padding:16px 20px;">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
                        <div>
                            <div class="td-sub">Title</div>
                            <div class="td-main">{{ $activity->title }}</div>
                        </div>
                        <div>
                            <div class="td-sub">Branch</div>
                            <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="td-sub">Type of Activity</div>
                            <div class="td-main">{{ $activity->type_of_activity ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="td-sub">Event Type</div>
                            <div class="td-main">{{ $activity->event_type ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="td-sub">Activity Level</div>
                            <div class="td-main">{{ $activity->activity_level ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="td-sub">Date</div>
                            <div class="td-main">{{ $activity->date_of_activity?->format('M j, Y') ?? '—' }}</div>
                        </div>
                        @if($activity->time_of_activity)
                        <div>
                            <div class="td-sub">Time</div>
                            <div class="td-main">{{ $activity->time_of_activity }}</div>
                        </div>
                        @endif
                        <div>
                            <div class="td-sub">Mode of Conduct</div>
                            <div class="td-main">{{ $activity->mode_of_conduct ?? '—' }}</div>
                        </div>
                        @if(in_array($activity->mode_of_conduct, ['Face to Face','Hybrid']) && $activity->venue)
                        <div>
                            <div class="td-sub">Venue</div>
                            <div class="td-main">{{ $activity->venue }} {{ $activity->venue_type ? "({$activity->venue_type})" : '' }}</div>
                        </div>
                        @endif
                        @if(in_array($activity->mode_of_conduct, ['Online','Hybrid']) && $activity->platform)
                        <div>
                            <div class="td-sub">Platform</div>
                            <div class="td-main">{{ $activity->platform }}</div>
                        </div>
                        @endif
                        @if($activity->participants_count)
                        <div>
                            <div class="td-sub">Participants</div>
                            <div class="td-main">{{ number_format($activity->participants_count) }}</div>
                        </div>
                        @endif
                        <div>
                            <div class="td-sub">Status</div>
                            @php
                                $sc = match($activity->status) {
                                    'pending'=>'b-pending','for approval'=>'b-for-approval',
                                    'approved'=>'b-approved','completed'=>'b-completed',
                                    'for revision'=>'b-revision',default=>'b-pending',
                                };
                            @endphp
                            <span class="badge {{ $sc }}">{{ ucfirst($activity->status) }}</span>
                        </div>
                    </div>

                    @if($activity->description)
                    <div style="margin-top:16px;">
                        <div class="td-sub">Description</div>
                        <div class="td-main">{{ $activity->description }}</div>
                    </div>
                    @endif

                    @if(is_array($activity->objectives) && count($activity->objectives))
                    <div style="margin-top:16px;">
                        <div class="td-sub">Objectives</div>
                        <ul style="margin:6px 0 0 16px; color:#374151; font-size:13px;">
                            @foreach($activity->objectives as $obj)
                                <li>{{ $obj }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Budget --}}
            <div class="show-section" style="margin-bottom:16px;">
                <div class="show-section-header" style="background:#eff6ff; border-color:#bfdbfe;">
                    <i class="fas fa-coins" style="color:#2563eb;"></i> Budget
                </div>
                <div style="padding:16px 20px;">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
                        <div>
                            <div class="td-sub">Funds</div>
                            <div class="td-main">{{ $activity->funds ?? '—' }}</div>
                        </div>
                        @if($activity->funds === 'With Budget' && $activity->source)
                        <div><div class="td-sub">Source</div><div class="td-main">{{ $activity->source }}</div></div>
                        @endif
                        @if(in_array($activity->funds, ['With Budget','ATC']) && $activity->amount !== null)
                        <div><div class="td-sub">Amount</div><div class="td-main">₱{{ number_format($activity->amount, 2) }}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Attachments --}}
            @if($activity->sarfDocuments->count())
            <div class="show-section">
                <div class="show-section-header" style="background:#fefce8; border-color:#fde68a;">
                    <i class="fas fa-paperclip" style="color:#d97706;"></i> Attachments
                    <a href="{{ route('branch_osa.sarf-documents.print-activity', $activity) }}" target="_blank" class="attachment-view-btn" style="margin-left:auto;">
                        <i class="fas fa-print"></i> Print All
                    </a>
                </div>
                <div style="padding:16px 20px;">
                    @foreach($activity->sarfDocuments as $doc)
                        <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f3f4f6;">
                            <span class="sarf-badge">{{ $doc->type }}</span>
                            <span style="font-size:13px; color:#374151;">{{ $doc->original_filename }}</span>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;">
                                <a href="{{ route('branch_osa.sarf-documents.show', $doc) }}" target="_blank" class="attachment-view-btn">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
