@extends($layout ?? 'Dean_OSA.layouts.layout')

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
            <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.activity.index') }}" class="panel-controls">
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
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.activity.index'])
                @include('Dean_OSA.partials.sarf-filters', [
                    'filterMode' => 'button',
                    'filterRoute' => ($routePrefix ?? 'dean_osa') . '.activity.index',
                    'pipelineStatuses' => [
                        'pending' => 'Pending',
                        'for revision' => 'For Revision',
                        'for reschedule' => 'For Rescheduling',
                    ],
                ])
                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> New Activity
                </a>
            </form>
        </div>

        @include('Dean_OSA.partials.sarf-filters', [
            'filterRoute' => ($routePrefix ?? 'dean_osa') . '.activity.index',
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
                                @if($activity->activityDateDisplay())
                                    <div class="td-main">
                                        <i class="fas fa-calendar-alt" style="color:#94a3b8; font-size:11px;"></i>
                                        {{ $activity->activityDateDisplay('M j, Y') }}
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
                                    <div class="td-sub">{{ implode(', ', $orgs) }}</div>
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
                                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View Activity Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($activity->status === 'for reschedule' || $activity->modification_type === 'rescheduling')
                                        <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.edit', $activity->id) }}"
                                            class="abtn abtn-resched" title="Reschedule Activity">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                    @elseif(in_array($activity->status, ['pending', 'for revision']))
                                        <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.edit', $activity->id) }}"
                                            class="abtn abtn-edit" title="Edit Activity">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->usertype !== 'Staff_OSA' && in_array($activity->status, ['pending', 'for revision', 'for reschedule']))
                                        <button type="button"
                                            class="abtn abtn-del"
                                            title="Delete Activity"
                                            onclick="openDeleteModal('{{ route(($routePrefix ?? 'dean_osa') . '.activity.destroy', $activity->id) }}', '{{ addslashes($activity->code) }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
                <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.activity.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.activity.index'])
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

{{-- DELETE MODAL --}}
<div class="mod-overlay" id="deleteOverlay" onclick="closeDeleteModal()">
    <div class="mod-modal delete-modal" onclick="event.stopPropagation()">
        <div class="mod-modal-header delete-modal-header">
            <div class="mod-modal-icon delete-modal-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div>
                <h3 class="mod-modal-title">Delete Activity</h3>
                <p class="mod-modal-subtitle" id="deleteSubtitle">SARF Code: -</p>
            </div>
            <button type="button" class="mod-close" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')

            <div class="mod-modal-body">
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>This will permanently delete the activity and its uploaded SARF documents.</strong>
                        <span>This action cannot be undone.</span>
                    </div>
                </div>
            </div>

            <div class="mod-modal-footer">
                <button type="button" class="btn btn-filter" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.mod-overlay {
    display:none;
    position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,0.55);
    backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
    animation:modFadeIn .2s ease;
}
.mod-overlay.active { display:flex; }

@keyframes modFadeIn  { from { opacity:0; } to { opacity:1; } }
@keyframes modSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

.mod-modal {
    background:#fff;
    border-radius:16px;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    width:520px; max-width:94vw;
    overflow:hidden;
    animation:modSlideUp .25s ease;
}
.mod-modal-header {
    display:flex; align-items:center; gap:14px;
    padding:20px 24px;
    background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%);
    border-bottom:1px solid #e0e7ff;
    position:relative;
}
.mod-modal-icon {
    width:44px; height:44px; border-radius:12px;
    background:#dbeafe; color:#1d4ed8;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
}
.mod-modal-title {
    font-size:17px; font-weight:700; color:#0f172a; margin:0;
}
.mod-modal-subtitle {
    font-size:12px; color:#64748b; margin:2px 0 0; font-weight:500;
}
.mod-close {
    position:absolute; top:16px; right:16px;
    background:none; border:none; cursor:pointer;
    color:#94a3b8; font-size:16px;
    width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s;
}
.mod-close:hover { background:#e2e8f0; color:#334155; }
.mod-modal-body { padding:24px; }
.mod-modal-footer {
    display:flex; justify-content:flex-end; gap:10px;
    padding:16px 24px;
    background:#f8fafc;
    border-top:1px solid #e5e7eb;
}
.delete-modal-header {
    background:linear-gradient(135deg,#fff1f2 0%,#ffe4e6 100%);
    border-bottom-color:#fecdd3;
}
.delete-modal-icon {
    background:#ffe4e6;
    color:#be123c;
}
.delete-warning {
    display:flex;
    align-items:flex-start;
    gap:12px;
    padding:14px 16px;
    border:1px solid #fecdd3;
    border-radius:12px;
    background:#fff1f2;
    color:#9f1239;
    font-size:13px;
    line-height:1.5;
}
.delete-warning i {
    color:#e11d48;
    margin-top:2px;
}
.delete-warning span {
    display:block;
    margin-top:2px;
    color:#be123c;
}
</style>

<script>
function openDeleteModal(action, code) {
    const overlay = document.getElementById('deleteOverlay');
    const form = document.getElementById('deleteForm');
    const subtitle = document.getElementById('deleteSubtitle');

    form.action = action;
    subtitle.textContent = 'SARF Code: ' + code;
    overlay.classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteOverlay').classList.remove('active');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>

@endsection
