@extends($layout ?? 'Dean_OSA.layouts.layout')

@section('title', 'Approvals | SARF Tracking')
@section('page-title', 'Approvals')

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @php
    /*
    |--------------------------------------------------------------------------
    | APPROVAL FIELDS — order matters (sequential)
    |--------------------------------------------------------------------------
    */
    $approvalFields = [
        ['col' => 'approval_dean_sa',       'rem' => 'remarks_dean_sa',        'office' => 'OSA',     'role' => 'Dean for Student Affairs'],
        ['col' => 'approval_avp_sps',       'rem' => 'remarks_avp_sps',        'office' => 'SPS',     'role' => 'Asst VP for Student Personnel Services'],
        ['col' => 'approval_dir_basic_ed',  'rem' => 'remarks_dir_basic_ed',   'office' => 'Basic Ed','role' => 'Director for Basic Education'],
        ['col' => 'approval_vp_acad',       'rem' => 'remarks_vp_acad',        'office' => 'Acad',    'role' => 'VP for Academic Affairs'],
        ['col' => 'approval_vp_hrd_legal',  'rem' => 'remarks_vp_hrd_legal',   'office' => 'Legal',   'role' => 'VP for HRD / Legal'],
        ['col' => 'approval_auditing',            'rem' => 'remarks_auditing',            'office' => 'Auditing',      'role' => 'Auditing'],
        ['col' => 'approval_comptroller_initial', 'rem' => 'remarks_comptroller_initial', 'office' => 'Comptroller 1', 'role' => 'Comptroller'],
        ['col' => 'approval_finance_initial',     'rem' => 'remarks_finance_initial',     'office' => 'Finance 1',     'role' => 'Finance'],
        ['col' => 'approval_osa_finance',         'rem' => 'remarks_osa_finance',         'office' => 'OSA Finance',   'role' => 'OSA'],
        ['col' => 'approval_finance_final',       'rem' => 'remarks_finance_final',       'office' => 'Finance 2',     'role' => 'Finance'],
        ['col' => 'approval_comptroller_final',   'rem' => 'remarks_comptroller_final',   'office' => 'Comptroller 2', 'role' => 'Comptroller'],
    ];

    $requiresBasicEdApproval = function($activity) {
        $levels = is_array($activity->level)
            ? $activity->level
            : (filled($activity->level) ? [$activity->level] : []);

        return collect($levels)->contains(function ($level) {
            $level = Str::lower((string) $level);
            return Str::contains($level, ['elementary','junior high','senior high','basic','all levels']);
        });
    };

    $requiresFinanceApproval = fn($activity) => $activity->funds === 'With Budget';
    $requiresLegalApproval = fn($activity) => $activity->waiver_consent === 'With';

    $isApprovalRequired = function($activity, $field) use ($requiresBasicEdApproval, $requiresFinanceApproval, $requiresLegalApproval) {
        if ($field === 'approval_dir_basic_ed') {
            return $requiresBasicEdApproval($activity);
        }

        if ($field === 'approval_vp_hrd_legal') {
            return $requiresLegalApproval($activity);
        }

        if (in_array($field, [
            'approval_auditing',
            'approval_comptroller_initial',
            'approval_finance_initial',
            'approval_osa_finance',
            'approval_finance_final',
            'approval_comptroller_final',
        ], true)) {
            return $requiresFinanceApproval($activity);
        }

        return true;
    };

    $getApplicableApprovalFields = fn($activity) => collect($approvalFields)
        ->filter(fn($sig) => $isApprovalRequired($activity, $sig['col']))
        ->values();

    /*
    |--------------------------------------------------------------------------
    | COMPUTED APPROVAL LOCATION
    | Returns the office where the SARF is currently sitting.
    | Only relevant when status = 'for approval' or 'for approval finance'.
    |--------------------------------------------------------------------------
    */
    $getApprovalLocation = function($activity) use ($getApplicableApprovalFields) {
        foreach ($getApplicableApprovalFields($activity) as $sig) {
            if (($activity->{$sig['col']} ?? 'pending') !== 'approved') {
                return $sig['office'];
            }
        }
        return null; // all approved
    };

    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE CONFIG
    |--------------------------------------------------------------------------
    */
    $locationOffices = collect($approvalFields)->pluck('office');

    $getStatusBadge = function($activity) use ($getApprovalLocation, $locationOffices) {
        $s        = $activity->status;
        $location = $getApprovalLocation($activity);
        $isForApproval = in_array($s, ['for approval', 'for approval finance']);

        if ($s === 'for approval for rescheduling') {
            return [
                'label' => 'Rescheduling',
                'bg'    => '#fef3c7',
                'color' => '#92400e',
                'border'=> '#fbbf24',
                'icon'  => 'fa-calendar-alt',
            ];
        }

        if ($isForApproval && $location) {
            return [
                'label' => 'Pending in ' . $location,
                'bg'    => '#dbeafe',
                'color' => '#014ea8',
                'border'=> '#93c5fd',
                'icon'  => 'fa-map-marker-alt',
            ];
        }

        return match($s) {
            'pending'      => ['label'=>'Pending',      'bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1','icon'=>'fa-clock'],
            'ongoing'      => ['label'=>'Ongoing',      'bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde68a','icon'=>'fa-spinner'],
            'for revision' => ['label'=>'For Revision', 'bg'=>'#fff1f2','color'=>'#da281c','border'=>'#fca5a5','icon'=>'fa-redo'],
            'approved'     => ['label'=>'Approved',     'bg'=>'#dcfce7','color'=>'#15803d','border'=>'#86efac','icon'=>'fa-check-circle'],
            'completed'    => ['label'=>'Completed',    'bg'=>'#f0fdf4','color'=>'#166534','border'=>'#4ade80','icon'=>'fa-check-double'],
            'cancelled'    => ['label'=>'Cancelled',    'bg'=>'#f8fafc','color'=>'#94a3b8','border'=>'#e2e8f0','icon'=>'fa-ban'],
            default        => ['label'=>ucfirst($s),    'bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1','icon'=>'fa-circle'],
        };
    };
    @endphp

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-check-circle"></i> SARF Approvals
            </div>
            <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.approval.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input class="search-input" type="text" name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search title or code…">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.approval.index'])
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'button', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.approval.index'])
            </form>
        </div>

        @include('Dean_OSA.partials.sarf-filters', ['filterRoute' => ($routePrefix ?? 'dean_osa') . '.approval.index'])

        {{-- ── Summary chips ── 
        <div style="display:flex; gap:8px; flex-wrap:wrap; padding:14px 20px; border-bottom:1px solid #e5e7eb;">
            @php
                $chipData = [
                    ['label'=>'Pending',     'status'=>'pending',              'bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1'],
                    ['label'=>'Ongoing',     'status'=>'ongoing',              'bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde68a'],
                    ['label'=>'For Approval','status'=>'for approval',         'bg'=>'#dbeafe','color'=>'#014ea8','border'=>'#93c5fd'],
                    ['label'=>'Finance',     'status'=>'for approval finance', 'bg'=>'#dbeafe','color'=>'#014ea8','border'=>'#93c5fd'],
                    ['label'=>'Approved',    'status'=>'approved',             'bg'=>'#dcfce7','color'=>'#15803d','border'=>'#86efac'],
                    ['label'=>'Completed',   'status'=>'completed',            'bg'=>'#f0fdf4','color'=>'#166534','border'=>'#4ade80'],
                    ['label'=>'For Revision','status'=>'for revision',         'bg'=>'#fff1f2','color'=>'#da281c','border'=>'#fca5a5'],
                    ['label'=>'Cancelled',   'status'=>'cancelled',            'bg'=>'#f8fafc','color'=>'#94a3b8','border'=>'#e2e8f0'],
                ];
            @endphp
            @foreach($chipData as $chip)
                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.approval.index', ['status' => $chip['status']]) }}"
                    style="text-decoration:none;">
                    <span style="
                        display:inline-flex; align-items:center; gap:5px;
                        font-size:12px; font-weight:600; padding:5px 12px;
                        border-radius:20px; cursor:pointer;
                        background:{{ $chip['bg'] }};
                        color:{{ $chip['color'] }};
                        border:1px solid {{ $chip['border'] }};
                        {{ request('status') === $chip['status'] ? 'outline:2px solid #014ea8; outline-offset:2px;' : '' }}
                        transition: all .15s;">
                        {{ $chip['label'] }}
                        <span style="background:rgba(0,0,0,0.08); border-radius:20px; padding:1px 7px; font-size:11px;">
                            {{ $counts[$chip['status']] ?? 0 }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>--}}

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Branch / Level</th>
                        <th>Activity Date</th>
                        <th>Funds</th>
                        <th>Approval Progress</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $applicableApprovalFields = $getApplicableApprovalFields($activity);
                            $dotStatuses    = $applicableApprovalFields->map(fn($s) => $activity->{$s['col']} ?? 'pending');
                            $approvedCount  = $dotStatuses->filter(fn($v) => $v === 'approved')->count();
                            $totalApprovals = $applicableApprovalFields->count();
                            $hasDisapproved = $dotStatuses->contains('disapproved');
                            $hasForSig      = $dotStatuses->contains('for signature');
                            $badge          = $getStatusBadge($activity);
                            $isForApproval  = in_array($activity->status, ['for approval','for approval finance']);
                            $canRequestRevision = in_array($activity->status, ['pending', 'ongoing', 'for approval', 'for approval finance'], true);
                            $canRequestRescheduling = $activity->status === 'approved';
                            $canRequestModification = ($canRequestRevision || $canRequestRescheduling)
                                && !in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature'], true);
                        @endphp
                        <tr>

                            {{-- Code --}}
                            <td style="white-space:nowrap;">
                                <span class="row-id">{{ $activity->code }}</span>
                            </td>

                            {{-- Title + pills --}}
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                                <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:4px;">
                                    @if($activity->type_of_activity)
                                        <span class="mini-pill pill-blue">{{ $activity->type_of_activity }}</span>
                                    @endif
                                    @if($activity->mode_of_conduct)
                                        <span class="mini-pill pill-slate">{{ $activity->mode_of_conduct }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Branch / Level --}}
                            <td>
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

                            {{-- Activity Date --}}
                            <td style="white-space:nowrap;">
                                <div class="td-main">
                                    {{ $activity->date_of_activity?->format('M j, Y') ?? '—' }}
                                </div>
                                @if($activity->time_of_activity)
                                    <div class="td-sub">{{ $activity->time_of_activity }}</div>
                                @endif
                            </td>

                            {{-- Funds --}}
                            <td>
                                @php
                                    $fundsClass = match($activity->funds) {
                                        'With Budget' => 'pill-green',
                                        'ATC'         => 'pill-amber',
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
                                @endif
                            </td>

                            {{-- Approval Progress dots --}}
                            <td style="min-width:170px;">

                                {{-- 7 dots --}}
                                <div style="display:flex; align-items:center; gap:5px; flex-wrap:nowrap;">
                                    @foreach($applicableApprovalFields as $i => $sig)
                                        @php
                                            $val = $activity->{$sig['col']} ?? 'pending';
                                            $requiredBefore = $applicableApprovalFields->take($i);
                                            $prevApproved = $requiredBefore->every(fn($previous) => ($activity->{$previous['col']} ?? 'pending') === 'approved');
                                            $isLocked     = !$prevApproved && $val === 'pending';

                                            $dotColor = match(true) {
                                                $val === 'approved'      => '#22c55e',
                                                $val === 'for signature' => '#014ea8',
                                                $val === 'disapproved'   => '#da281c',
                                                $isLocked                => '#94a3b8',
                                                default                  => '#94a3b8',
                                            };
                                            $dotTitle = $sig['role'] . ': ' . match($val) {
                                                'approved'      => 'Approved',
                                                'for signature' => 'For Signature',
                                                'disapproved'   => 'Disapproved',
                                                default         => 'Pending',
                                            };
                                        @endphp
                                        <div title="{{ $dotTitle }}"
                                            style="width:10px; height:10px; border-radius:50%;
                                                background:{{ $dotColor }}; flex-shrink:0;
                                                cursor:default; transition:transform .15s;"
                                            onmouseover="this.style.transform='scale(1.35)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                        </div>
                                    @endforeach
                                    <span style="font-size:10.5px; font-weight:700; color:#64748b;
                                                 margin-left:3px; white-space:nowrap;">
                                        {{ $approvedCount }}/{{ $totalApprovals }}
                                    </span>
                                </div>

                                {{-- Sub label --}}
                                <div style="margin-top:5px;">
                                    @if($approvedCount === $totalApprovals)
                                        <span style="font-size:11px; font-weight:600; color:#15803d;">
                                            <i class="fas fa-check-circle"></i> All approved
                                        </span>
                                    @elseif($hasDisapproved)
                                        <span style="font-size:11px; font-weight:600; color:#da281c;">
                                            <i class="fas fa-times-circle"></i> Disapproved
                                        </span>
                                    @elseif($hasForSig)
                                        <span style="font-size:11px; font-weight:600; color:#014ea8;">
                                            <i class="fas fa-pen-nib"></i> For signature
                                        </span>
                                    @elseif($isForApproval)
                                        <span style="font-size:11px; font-weight:600; color:#94a3b8;">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @else
                                        <span style="font-size:11px; color:#cbd5e1;">
                                            <i class="fas fa-minus"></i> Not started
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status (computed) --}}
                            <td style="white-space:nowrap;">
                                <span style="
                                    display:inline-flex; align-items:center; gap:5px;
                                    font-size:11.5px; font-weight:700;
                                    padding:4px 10px; border-radius:20px;
                                    background:{{ $badge['bg'] }};
                                    color:{{ $badge['color'] }};
                                    border:1px solid {{ $badge['border'] }};">
                                    <i class="fas {{ $badge['icon'] }}" style="font-size:10px;"></i>
                                    {{ $badge['label'] }}
                                </span>
                                @if($activity->status !== 'for approval for rescheduling' && in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature'], true))
                                @php
                                    $rescheduleLabel = match($activity->reschedule_status) {
                                        'for approval' => 'Reschedule For Approval',
                                        'for signature' => 'Reschedule For Signature',
                                        default => 'Reschedule Request',
                                    };
                                @endphp
                                <span style="
                                    display:inline-flex; align-items:center; gap:4px;
                                    font-size:10px; font-weight:700;
                                    padding:3px 8px; border-radius:20px;
                                    background:#fef3c7; color:#92400e;
                                    border:1px solid #fbbf24; margin-left:4px;">
                                    <i class="fas fa-calendar-alt" style="font-size:9px;"></i>
                                    {{ $rescheduleLabel }}
                                </span>
                                @endif
                            </td>


                            {{-- Actions --}}
                            <td style="white-space:nowrap;">
                                <div class="action-cell" style="display:inline-flex; gap:8px; align-items:center; flex-wrap:nowrap; white-space:nowrap;">
                                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.approval.review', $activity->id) }}"
                                        class="abtn abtn-view" title="Review & Approve Activity" style="flex-shrink:0;">
                                        <i class="fas fa-stamp"></i>
                                    </a>
                                    @if($canRequestModification)
                                        <button type="button"
                                            class="abtn abtn-mod"
                                            title="Request Modification"
                                            style="flex-shrink:0;"
                                            onclick="openModificationModal({{ $activity->id }}, '{{ addslashes($activity->code) }}', {{ $canRequestRevision ? 'true' : 'false' }}, {{ $canRequestRescheduling ? 'true' : 'false' }})">
                                            <i class="ti ti-adjustments-horizontal"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()?->usertype !== 'Staff_OSA')
                                        <button type="button"
                                            class="abtn abtn-del"
                                            title="Delete Activity"
                                            style="flex-shrink:0;"
                                            onclick="openDeleteModal('{{ route(($routePrefix ?? 'dean_osa') . '.approval.destroy', $activity->id) }}', '{{ addslashes($activity->code) }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="td-muted" style="text-align:center; padding:40px;">
                                <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px; color:#e2e8f0;"></i>
                                No activities found.
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
                <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.approval.index') }}" class="show-wrap">
                    @if(request('search'))  <input type="hidden" name="search"   value="{{ request('search') }}">  @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.approval.index'])
                    Show
                    <select name="per_page" onchange="this.form.submit()">
                        <option value="10"  @selected(request('per_page',10)==10)>10</option>
                        <option value="25"  @selected(request('per_page')==25)>25</option>
                        <option value="50"  @selected(request('per_page')==50)>50</option>
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
                                Activity returns to approval after changes.
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
                                Requires schedule approval before returning.
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

/* ── Dot legend bar ── */
.dot-legend {
    display:flex; gap:16px; flex-wrap:wrap; align-items:center;
    padding:8px 20px; border-bottom:1px solid #f1f5f9;
    background:#fafafa;
}
.dot-legend-item {
    display:flex; align-items:center; gap:5px;
    font-size:11.5px; color:#64748b; font-weight:500;
}
.dot-legend-dot {
    width:10px; height:10px; border-radius:50%; flex-shrink:0;
}

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

    form.action = `{{ url(($routePrefix ?? 'dean_osa') . '/approval') }}/${activityId}/modification`;
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

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModificationModal();
        closeDeleteModal();
    }
});
</script>
@endsection
