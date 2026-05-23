@extends('Dean_OSA.layouts.layout')

@section('title', 'Org Activities | SARF Tracking')
@section('page-title', 'Org Activities')

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-file-alt"></i> SARF Requests</div>
            <form method="GET" action="{{ route('dean_osa.activity.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search title, code, status…">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => 'dean_osa.activity.index'])
                @include('Dean_OSA.partials.sarf-filters', [
                    'filterMode' => 'button',
                    'filterRoute' => 'dean_osa.activity.index',
                    'pipelineStatuses' => [
                        'pending' => 'Pending',
                        'for revision' => 'For Revision',
                        'for reschedule' => 'For Rescheduling',
                    ],
                ])
                <a href="{{ route('dean_osa.activity.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> New Activity
                </a>
            </form>
        </div>

        @include('Dean_OSA.partials.sarf-filters', [
            'filterRoute' => 'dean_osa.activity.index',
            'pipelineStatuses' => [
                'pending' => 'Pending',
                'for revision' => 'For Revision',
                'for reschedule' => 'For Rescheduling',
            ],
        ])

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Date & Venue</th>
                        <th class="branch-level-col">Branch / Level</th>
                        <th>Participants</th>
                        <th>Funds</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>

                            {{-- Code only, no school year --}}
                            <td style="white-space:nowrap;">
                                <span class="row-id">{{ $activity->code }}</span>
                            </td>

                            {{-- Title + type + mode + event type --}}
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                                <div class="td-sub" style="display:flex; gap:5px; flex-wrap:wrap; margin-top:4px;">
                                    @if($activity->type_of_activity)
                                        <span class="mini-pill pill-blue">{{ $activity->type_of_activity }}</span>
                                    @endif
                                    @if($activity->mode_of_conduct)
                                        <span class="mini-pill pill-slate">{{ $activity->mode_of_conduct }}</span>
                                    @endif
                                    @if($activity->event_type)
                                        <span class="mini-pill pill-slate">{{ $activity->event_type }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Date + time + venue/platform --}}
                            <td style="white-space:nowrap;">
                                @if($activity->date_of_activity)
                                    <div class="td-main">
                                        <i class="fas fa-calendar-alt" style="color:#94a3b8; font-size:11px;"></i>
                                        {{ $activity->date_of_activity->format('M j, Y') }}
                                    </div>
                                @else
                                    <div class="td-main">—</div>
                                @endif
                                @if($activity->time_of_activity)
                                    <div class="td-sub">
                                        <i class="fas fa-clock" style="font-size:10px;"></i>
                                        {{ $activity->time_of_activity }}
                                    </div>
                                @endif
                                @if($activity->venue)
                                    <div class="td-sub">
                                        <i class="fas fa-map-marker-alt" style="font-size:10px;"></i>
                                        {{ Str::limit($activity->venue, 24) }}
                                        @if($activity->venue_type)
                                            ({{ $activity->venue_type }})
                                        @endif
                                    </div>
                                @elseif($activity->platform)
                                    <div class="td-sub">
                                        <i class="fas fa-video" style="font-size:10px;"></i>
                                        {{ $activity->platform }}
                                    </div>
                                @endif
                            </td>

                            {{-- Branch / Level --}}
                            <td class="branch-level-col">
                                <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
                                @php
                                    $levels = is_array($activity->level) ? $activity->level : [];
                                    $departments = is_array($activity->department)
                                        ? $activity->department
                                        : (filled($activity->department) ? [$activity->department] : []);
                                    $orgs = is_array($activity->organizations)
                                        ? $activity->organizations
                                        : (filled($activity->organizations) ? [$activity->organizations] : []);
                                @endphp
                                @if(count($levels))
                                    <div class="td-sub">{{ implode(', ', $levels) }}</div>
                                @endif
                                @if(count($departments))
                                    <div class="td-sub">{{ implode(', ', $departments) }}</div>
                                @endif
                                @if(count($orgs))
                                    <div class="td-sub" style="color:#8b5cf6;">{{ implode(', ', $orgs) }}</div>
                                @endif
                            </td>


                            {{-- Participants count + profile --}}
                            <td style="text-align:center;">
                                @if($activity->participants_count)
                                    <div class="td-main" style="font-size:16px; font-weight:700; color:#3b82f6;">
                                        {{ number_format($activity->participants_count) }}
                                    </div>
                                @else
                                    <div class="td-main">—</div>
                                @endif
                                @if($activity->participants_profile)
                                    <div class="td-sub">{{ Str::limit($activity->participants_profile, 22) }}</div>
                                @endif
                            </td>

                            {{-- Funds + source or amount --}}
                            <td>
                                @php
                                    $fundsClass = match($activity->funds) {
                                        'With Budget' => 'pill-green',
                                        'ATC'         => 'pill-amber',
                                        'No Fee'      => 'pill-slate',
                                        default       => 'pill-slate',
                                    };
                                @endphp
                                @if($activity->funds)
                                    <span class="mini-pill {{ $fundsClass }}">{{ $activity->funds }}</span>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                                @if($activity->source)
                                    <div class="td-sub">{{ $activity->source }}</div>
                                @elseif($activity->amount)
                                    <div class="td-sub">₱{{ number_format($activity->amount, 2) }}</div>
                                @endif
                            </td>

                            {{-- Status + submitted date WITH time --}}
                            <td>
                                @include('partials.sarf-status-badge', ['activity' => $activity])
                                @if($activity->modification_type === 'rescheduling' && $activity->status !== 'for reschedule')
                                    <span class="mini-pill pill-amber" style="margin-left:3px;">
                                        <i class="fas fa-calendar-alt" style="font-size:9px;"></i>
                                        {{ ucfirst($activity->modification_type) }}
                                    </span>
                                @endif
                                @if($activity->created_at)
                                    <div class="td-sub" style="margin-top:4px;">
                                        {{ $activity->created_at->format('M j, Y') }}
                                    </div>
                                    <div class="td-sub">
                                        {{ $activity->created_at->format('g:i A') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route('dean_osa.activity.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View Activity Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($activity->status === 'for reschedule' || $activity->modification_type === 'rescheduling')
                                        <a href="{{ route('dean_osa.activity.edit', $activity->id) }}"
                                            class="abtn abtn-resched" title="Reschedule Activity">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                    @elseif(in_array($activity->status, ['pending', 'for revision']))
                                        <a href="{{ route('dean_osa.activity.edit', $activity->id) }}"
                                            class="abtn abtn-edit" title="Edit Activity">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endif
                                    @if(in_array($activity->status, ['pending', 'for revision', 'for reschedule']))
                                        <form action="{{ route('dean_osa.activity.destroy', $activity->id) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this activity? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="abtn abtn-del" title="Delete Activity">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="td-muted" style="text-align:center; padding:40px;">
                                No activities found. Click <strong>New Activity</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">
                    Showing {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }}
                    of {{ $activities->total() }} entries
                </span>
                <form method="GET" action="{{ route('dean_osa.activity.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => 'dean_osa.activity.index'])
                    Show
                    <select name="per_page" onchange="this.form.submit()">
                        <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                        <option value="25" @selected(request('per_page') == 25)>25</option>
                        <option value="50" @selected(request('per_page') == 50)>50</option>
                    </select>
                    entries
                </form>
            </div>
            <div class="pagi">
                @if($activities->onFirstPage())
                    <span class="pbtn pd">&#8249; Previous</span>
                @else
                    <a class="pbtn" href="{{ $activities->previousPageUrl() }}">&#8249; Previous</a>
                @endif

                @foreach($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                    @if($page == $activities->currentPage())
                        <span class="pbtn pa">{{ $page }}</span>
                    @else
                        <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($activities->hasMorePages())
                    <a class="pbtn" href="{{ $activities->nextPageUrl() }}">Next &#8250;</a>
                @else
                    <span class="pbtn pd">Next &#8250;</span>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
