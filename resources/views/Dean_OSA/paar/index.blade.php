@extends($layout ?? 'Dean_OSA.layouts.layout')

@section('title', 'Post-Activity Reports | SARF Tracking')
@section('page-title', 'Post-Activity Reports')

@section('content')
@php
    $paarDocumentTypes = fn ($activity) => in_array($activity->funds, ['With Budget', 'ATC'], true)
        ? ['PAAR_LIQUIDATION', 'PAAR_NARRATIVE_REPORT', 'PAAR_PHOTO_DOCUMENTS', 'PAAR_SUMMARY_REPORT']
        : ['PAAR_NARRATIVE_REPORT', 'PAAR_PHOTO_DOCUMENTS', 'PAAR_SUMMARY_REPORT'];
@endphp

<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <b>{{ $errors->first() }}</b>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-medical"></i> Completed Activities for PAAR
            </div>
            <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.paar.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search code or activity">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.paar.index'])
                @include('Dean_OSA.partials.sarf-filters', [
                    'filterMode' => 'button',
                    'filterRoute' => ($routePrefix ?? 'dean_osa') . '.paar.index',
                    'pipelineStatuses' => ['completed' => 'Completed'],
                ])
            </form>
        </div>

        @include('Dean_OSA.partials.sarf-filters', [
            'filterRoute' => ($routePrefix ?? 'dean_osa') . '.paar.index',
            'pipelineStatuses' => ['completed' => 'Completed'],
        ])

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Branch / Level</th>
                        <th>Activity Date</th>
                        <th>Funds</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $hasPaarInput = $activity->sarfDocuments
                                ->whereIn('type', $paarDocumentTypes($activity))
                                ->isNotEmpty();
                            $canRequestModification = $activity->status === 'approved';
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                <span class="row-id">{{ $activity->code }}</span>
                            </td>
                             {{-- Title + type + mode + event type --}}
<td style="max-width: 220px; word-break: break-word;">
    <div class="td-name" style="font-weight: 600;">{{ $activity->title }}</div>
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

                            {{-- Activity Date + venue/platform --}}
                            <td style="white-space:nowrap;">
                                <div class="td-main">
                                    {{ $activity->activityDateDisplay('M j, Y', ', ', 2) ?? '—' }}
                                </div>
                                @if($activity->activityTimeDisplay(', ', 2))
                                    <div class="td-sub">
                                        <i class="fas fa-clock" style="font-size:10px;"></i>
                                        {{ $activity->activityTimeDisplay(', ', 2) }}
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

                             {{-- Status --}}
                            <td>
                                @include('partials.sarf-status-badge', ['activity' => $activity])
                            </td>
                            <td style="white-space:nowrap;">
                                @if($activity->created_at)
                                    <div class="td-main">{{ $activity->created_at->format('M j, Y') }}</div>
                                    <div class="td-sub">{{ $activity->created_at->format('g:i A') }}</div>
                                @else
                                    <span class="td-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View PAAR Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($hasPaarInput)
                                        <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.edit', $activity->id) }}"
                                            class="abtn abtn-edit" title="Edit PAAR">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @else
                                        <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.act', $activity->id) }}"
                                            class="abtn abtn-approve" title="Add Accomplishment">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    @endif
                                    @if($canRequestModification)
                                        <button type="button"
                                            class="abtn abtn-mod"
                                            title="Request Modification"
                                            style="flex-shrink:0;"
                                            onclick="openModificationModal({{ $activity->id }}, '{{ addslashes($activity->code) }}', true, true)">
                                            <i class="ti ti-adjustments-horizontal"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="td-muted" style="text-align:center; padding:40px;">
                                No completed activities ready for Post-Activity Report.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">
                    Showing {{ $activities->firstItem() ?? 0 }}&ndash;{{ $activities->lastItem() ?? 0 }}
                    of {{ $activities->total() }} entries
                </span>
                <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.paar.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.paar.index'])
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

{{-- ══════════════════════════════════════════════
     MODIFICATION MODAL
══════════════════════════════════════════════ --}}
<div class="mod-overlay" id="modOverlay" onclick="closeModificationModal()">
    <div class="mod-modal" onclick="event.stopPropagation()">
        <div class="mod-modal-header">
            <div class="mod-modal-icon">
                <i class="ti ti-adjustments-horizontal"></i>
            </div>
            <div>
                <h3 class="mod-modal-title">Request Modification</h3>
                <p class="mod-modal-subtitle" id="modSubtitle">SARF Code: —</p>
            </div>
            <button type="button" class="mod-close" onclick="closeModificationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="modForm" method="POST" action="">
            @csrf

            <div class="mod-modal-body">
                <p class="mod-label">What type of modification?</p>

                <div class="mod-type-cards">
                    <label class="mod-type-card" id="modCardRevision">
                        <input type="radio" name="modification_type" value="revision" required
                            onchange="selectModType(this.value)">
                        <div class="mod-type-card-inner">
                            <div class="mod-type-icon" style="background:#dbeafe; color:#1d4ed8;">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="mod-type-label">Revision</div>
                            <div class="mod-type-desc">
                                Send back for content edits.<br>
                                Activity returns to Activities.
                            </div>
                        </div>
                    </label>

                    <label class="mod-type-card" id="modCardRescheduling">
                        <input type="radio" name="modification_type" value="rescheduling" required
                            onchange="selectModType(this.value)">
                        <div class="mod-type-card-inner">
                            <div class="mod-type-icon" style="background:#fef3c7; color:#92400e;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="mod-type-label">Rescheduling</div>
                            <div class="mod-type-desc">
                                Change schedule details.<br>
                                Returns to Activities then Approvals.
                            </div>
                        </div>
                    </label>
                </div>

                <div class="mod-remarks-wrap">
                    <label class="mod-label" for="modRemarks">Remarks / Instructions <span style="color:#94a3b8; font-weight:400;">(optional)</span></label>
                    <textarea name="modification_remarks" id="modRemarks" class="form-control"
                        rows="3" maxlength="1000"
                        placeholder="Describe what needs to be modified…"></textarea>
                </div>
            </div>

            <div class="mod-modal-footer">
                <button type="button" class="btn btn-filter" onclick="closeModificationModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-add" id="modSubmitBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Send for Modification
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Cell helpers ── */
.td-main  { font-size:13.5px; font-weight:600; color:#1e293b; }
.td-sub   { font-size:11.5px; color:#94a3b8; margin-top:2px; line-height:1.4; }
.td-muted { color:#94a3b8; font-style:italic; }

/* ── Mini pills ── */
.mini-pill {
    display:inline-block; font-size:11px; font-weight:600;
    border-radius:20px; padding:2px 8px; white-space:nowrap;
}
.pill-blue  { background:#dbeafe; color:#1d4ed8; }
.pill-slate { background:#f1f5f9; color:#475569; }
.pill-green { background:#dcfce7; color:#15803d; }
.pill-amber { background:#fef9c3; color:#92400e; }

/* ══════════════════════════════════════════════
   MODIFICATION MODAL
══════════════════════════════════════════════ */
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
.mod-label {
    display:block; font-size:13px; font-weight:600; color:#334155;
    margin-bottom:10px;
}

/* ── Type selection cards ── */
.mod-type-cards {
    display:grid; grid-template-columns:1fr 1fr; gap:12px;
    margin-bottom:20px;
}
.mod-type-card {
    cursor:pointer;
}
.mod-type-card.is-disabled {
    cursor:not-allowed;
    opacity:0.48;
}
.mod-type-card.is-disabled .mod-type-card-inner,
.mod-type-card.is-disabled .mod-type-card-inner:hover {
    border-color:#e2e8f0;
    background:#f8fafc;
    box-shadow:none;
}
.mod-type-card input { display:none; }
.mod-type-card-inner {
    border:2px solid #e2e8f0;
    border-radius:12px;
    padding:18px 14px;
    text-align:center;
    transition:all .2s;
    background:#fafbfc;
}
.mod-type-card-inner:hover {
    border-color:#93c5fd;
    background:#f0f9ff;
}
.mod-type-card input:checked ~ .mod-type-card-inner {
    border-color:#3b82f6;
    background:#eff6ff;
    box-shadow:0 0 0 3px rgba(59,130,246,0.15);
}
.mod-type-icon {
    width:44px; height:44px; border-radius:12px;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:18px; margin-bottom:10px;
}
.mod-type-label {
    font-size:14px; font-weight:700; color:#0f172a; margin-bottom:4px;
}
.mod-type-desc {
    font-size:11.5px; color:#64748b; line-height:1.5;
}

.mod-remarks-wrap { margin-top:4px; }
.mod-remarks-wrap textarea {
    resize:vertical; min-height:70px;
    border-radius:10px; font-size:13px;
}

.mod-modal-footer {
    display:flex; justify-content:flex-end; gap:10px;
    padding:16px 24px;
    background:#f8fafc;
    border-top:1px solid #e5e7eb;
}
</style>

<script>
/* ══════════════════════════════════════════════
   Modification Modal Logic
══════════════════════════════════════════════ */
function openModificationModal(activityId, code, canRevision, canRescheduling) {
    const overlay = document.getElementById('modOverlay');
    const form    = document.getElementById('modForm');
    const subtitle = document.getElementById('modSubtitle');
    const revisionCard = document.getElementById('modCardRevision');
    const reschedulingCard = document.getElementById('modCardRescheduling');
    const revisionInput = revisionCard?.querySelector('input');
    const reschedulingInput = reschedulingCard?.querySelector('input');

    form.action = `{{ url(($routePrefix ?? 'dean_osa') . '/paar') }}/${activityId}/modification`;
    subtitle.textContent = 'SARF Code: ' + code;

    // Reset state
    form.reset();
    document.getElementById('modSubmitBtn').disabled = true;
    document.querySelectorAll('.mod-type-card input').forEach(r => r.checked = false);
    revisionCard?.classList.toggle('is-disabled', !canRevision);
    reschedulingCard?.classList.toggle('is-disabled', !canRescheduling);

    if (revisionInput) revisionInput.disabled = !canRevision;
    if (reschedulingInput) reschedulingInput.disabled = !canRescheduling;

    overlay.classList.add('active');
}

function closeModificationModal() {
    document.getElementById('modOverlay').classList.remove('active');
}

function selectModType(val) {
    document.getElementById('modSubmitBtn').disabled = false;
}

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModificationModal();
    }
});
</script>
@endsection
