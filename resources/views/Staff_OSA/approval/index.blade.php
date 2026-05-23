@extends('Staff_OSA.layouts.layout')

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

    $isApprovalRequired = function($activity, $field) use ($requiresBasicEdApproval, $requiresFinanceApproval) {
        if ($field === 'approval_dir_basic_ed') {
            return $requiresBasicEdApproval($activity);
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
            <form method="GET" action="{{ route('dean_osa.approval.index') }}" class="panel-controls">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['pending','ongoing','for approval','for approval finance','approved','completed','for revision','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input class="search-input" type="text" name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search title or code…">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>

        {{-- ── Summary chips ── --}}
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
                <a href="{{ route('dean_osa.approval.index', ['status' => $chip['status']]) }}"
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
        </div>

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
                        <th>Submitted</th>
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
                            {{-- Branch / Level --}}
                            <td>
                                <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
                                @php
                                    $levels = is_array($activity->level) ? $activity->level : [];
                                    $departments = is_array($activity->department)
                                        ? $activity->department
                                        : (filled($activity->department) ? [$activity->department] : []);
                                @endphp
                                @if(count($levels))
                                    <div class="td-sub">{{ implode(', ', $levels) }}</div>
                                @endif
                                @if(count($departments))
                                    <div class="td-sub" >{{implode(', ', $departments)}}</div>
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

                                            // locked = previous not approved AND not yet started
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
                                            style="
                                                width:12px; height:12px;
                                                border-radius:50%;
                                                background:{{ $dotColor }};
                                                flex-shrink:0;
                                                box-shadow:0 0 0 2px {{ $dotColor }}33;
                                                transition: transform .15s;
                                                cursor:default;"
                                            onmouseover="this.style.transform='scale(1.4)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                        </div>
                                    @endforeach

                                    {{-- Count --}}
                                    <span style="font-size:11px; font-weight:700; color:#64748b;
                                                 margin-left:4px; white-space:nowrap;">
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
                                @include('partials.sarf-status-badge', ['activity' => $activity])
                            </td>

                            {{-- Submitted --}}
                            <td style="white-space:nowrap;">
                                <div class="td-main">{{ $activity->created_at?->format('M j, Y') ?? '—' }}</div>
                                <div class="td-sub">{{ $activity->created_at?->format('g:i A') ?? '' }}</div>
                            </td>

                            {{-- Actions --}}
                            <td style="white-space:nowrap;">
                                <div class="action-cell" >
                                    <a href="{{ route('dean_osa.approval.review', $activity->id) }}"
                                        class="abtn abtn-view" title="Review & Approve Activity">
                                        <i class="fas fa-stamp"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="td-muted" style="text-align:center; padding:40px;">
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
                <form method="GET" action="{{ route('dean_osa.approval.index') }}" class="show-wrap">
                    @if(request('search'))  <input type="hidden" name="search"   value="{{ request('search') }}">  @endif
                    @if(request('status'))  <input type="hidden" name="status"   value="{{ request('status') }}">  @endif
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

@endsection
