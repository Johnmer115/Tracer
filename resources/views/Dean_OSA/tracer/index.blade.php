@extends('Dean_OSA.layouts.layout')

@section('title', 'Org Activities | SARF Tracking')
@section('page-title', 'Org Activities')

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @php
        $approvalFields = [
            ['col' => 'approval_dean_sa',             'office' => 'OSA',           'role' => 'Dean for Student Affairs'],
            ['col' => 'approval_avp_sps',             'office' => 'SPS',           'role' => 'Asst VP for Student Personnel Services'],
            ['col' => 'approval_dir_basic_ed',        'office' => 'Basic Ed',      'role' => 'Director for Basic Education'],
            ['col' => 'approval_vp_acad',             'office' => 'Acad',          'role' => 'VP for Academic Affairs'],
            ['col' => 'approval_vp_hrd_legal',        'office' => 'Legal',         'role' => 'VP for HRD / Legal'],
            ['col' => 'approval_auditing',            'office' => 'Auditing',      'role' => 'Auditing'],
            ['col' => 'approval_comptroller_initial', 'office' => 'Comptroller 1', 'role' => 'Comptroller'],
            ['col' => 'approval_finance_initial',     'office' => 'Finance 1',     'role' => 'Finance'],
            ['col' => 'approval_osa_finance',         'office' => 'OSA Finance',   'role' => 'OSA'],
            ['col' => 'approval_finance_final',       'office' => 'Finance 2',     'role' => 'Finance'],
            ['col' => 'approval_comptroller_final',   'office' => 'Comptroller 2', 'role' => 'Comptroller'],
        ];

        $requiresBasicEdApproval = function($activity) {
            $levels = is_array($activity->level)
                ? $activity->level
                : (filled($activity->level) ? [$activity->level] : []);
            return collect($levels)->contains(function ($level) {
                $level = Str::lower((string) $level);
                return Str::contains($level, ['elementary', 'junior high', 'senior high', 'basic', 'all levels']);
            });
        };

        $requiresFinanceApproval = fn($activity) => $activity->funds === 'With Budget';

        $getApplicableApprovalFields = function($activity) use ($approvalFields, $requiresBasicEdApproval, $requiresFinanceApproval) {
            return collect($approvalFields)->filter(function($sig) use ($activity, $requiresBasicEdApproval, $requiresFinanceApproval) {
                if ($sig['col'] === 'approval_dir_basic_ed') {
                    return $requiresBasicEdApproval($activity);
                }
                if (in_array($sig['col'], [
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
            })->values();
        };

        $getApprovalLocation = function($activity) use ($getApplicableApprovalFields) {
            foreach ($getApplicableApprovalFields($activity) as $sig) {
                if (($activity->{$sig['col']} ?? 'pending') !== 'approved') {
                    return $sig['office'];
                }
            }
            return null;
        };

        $getStatusBadge = function($activity) use ($getApprovalLocation) {
            $status   = $activity->status;
            $location = $getApprovalLocation($activity);

            if (in_array($status, ['for approval', 'for approval finance'], true) && $location) {
                return [
                    'label'  => 'Pending in ' . $location,
                    'bg'     => '#dbeafe',
                    'color'  => '#014ea8',
                    'border' => '#93c5fd',
                    'icon'   => 'fa-map-marker-alt',
                ];
            }

            return match($status) {
                'pending'               => ['label' => 'Pending',          'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-clock'],
                'ongoing'              => ['label' => 'Ongoing',           'bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde68a', 'icon' => 'fa-spinner'],
                'for approval'         => ['label' => 'For Approval',      'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-clipboard-check'],
                'for approval finance' => ['label' => 'Finance Approval',  'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-file-invoice-dollar'],
                'for revision'         => ['label' => 'For Revision',      'bg' => '#fff1f2', 'color' => '#da281c', 'border' => '#fca5a5', 'icon' => 'fa-redo'],
                'approved'             => ['label' => 'Approved',          'bg' => '#dcfce7', 'color' => '#15803d', 'border' => '#86efac', 'icon' => 'fa-check-circle'],
                'completed'            => ['label' => 'Completed',         'bg' => '#f0fdf4', 'color' => '#166534', 'border' => '#4ade80', 'icon' => 'fa-check-double'],
                'cancelled'            => ['label' => 'Cancelled',         'bg' => '#f8fafc', 'color' => '#94a3b8', 'border' => '#e2e8f0', 'icon' => 'fa-ban'],
                default                => ['label' => ucfirst((string) $status), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-circle'],
            };
        };

        // Group the current page's records by branch name
        $groupedByBranch = $activities->getCollection()
            ->groupBy(fn($a) => $a->branch->name ?? 'Unknown Branch');
    @endphp

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-file-alt"></i> SARF Requests</div>
            <form method="GET" action="{{ route('dean_osa.tracer.index') }}" class="panel-controls">
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
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden',  'filterRoute' => 'dean_osa.tracer.index'])
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'button',  'filterRoute' => 'dean_osa.tracer.index'])
                <a href="{{ route('dean_osa.activity.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> New Activity
                </a>
            </form>
        </div>

        {{-- Active filter chips + filter drawer --}}
        @include('Dean_OSA.partials.sarf-filters', ['filterRoute' => 'dean_osa.tracer.index'])

        {{-- ── Branch-grouped tables ── --}}
        @forelse($groupedByBranch as $branchName => $branchActivities)

            <div style="
                display:flex; align-items:center; gap:10px;
                padding:10px 14px; margin-bottom:10px; margin-top:6px;
                background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                <button
                    type="button"
                    onclick="toggleBranch('{{ Str::slug($branchName) }}')"
                    style="display:flex; align-items:center; gap:10px; background:none; border:none; cursor:pointer; width:100%; text-align:left; padding:0;">
                    <span style="
                        display:inline-flex; align-items:center; justify-content:center;
                        width:30px; height:30px; border-radius:8px;
                        background:#014ea8; color:#fff; font-size:13px; flex-shrink:0;">
                        <i class="fas fa-code-branch"></i>
                    </span>
                    <span style="font-size:15px; font-weight:700; color:#1e293b;">{{ $branchName }}</span>
                    <span style="
                        font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px;
                        background:#e0e7ff; color:#3730a3; margin-left:2px;">
                        {{ $branchActivities->count() }} {{ Str::plural('activity', $branchActivities->count()) }}
                    </span>
                    <i class="fas fa-chevron-up"
                       id="chevron-{{ Str::slug($branchName) }}"
                       style="margin-left:auto; font-size:12px; color:#94a3b8; transition:transform 0.2s;"></i>
                </button>
            </div>

            <div id="branch-{{ Str::slug($branchName) }}" style="margin-bottom:28px;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Activity</th>
                                <th>Level / Dept / Org</th>
                                <th>Activity Date</th>
                                <th>Funds</th>
                                <th>Approval Progress</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchActivities as $activity)
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
                                    <td style="white-space:nowrap;">
                                        <span class="row-id">{{ $activity->code }}</span>
                                    </td>
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
                                    <td>
                                        @php
                                            $levels      = is_array($activity->level) ? $activity->level : [];
                                            $departments = is_array($activity->department)
                                                ? $activity->department
                                                : (filled($activity->department) ? [$activity->department] : []);
                                            $orgs        = is_array($activity->organizations)
                                                ? $activity->organizations
                                                : (filled($activity->organizations) ? [$activity->organizations] : []);
                                        @endphp
                                        @if(count($levels))
                                            <div class="td-main">{{ implode(', ', $levels) }}</div>
                                        @endif
                                        @if(count($departments))
                                            <div class="td-sub">{{ implode(', ', $departments) }}</div>
                                        @endif
                                        @if(count($orgs))
                                            <div class="td-sub" style="color:#8b5cf6;">{{ implode(', ', $orgs) }}</div>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <div class="td-main">{{ $activity->date_of_activity?->format('M j, Y') ?? '—' }}</div>
                                        @if($activity->time_of_activity)
                                            <div class="td-sub">{{ $activity->time_of_activity }}</div>
                                        @endif
                                    </td>
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
                                    <td style="min-width:170px;">
                                        <div style="display:flex; align-items:center; gap:5px; flex-wrap:nowrap;">
                                            @foreach($applicableApprovalFields as $i => $sig)
                                                @php
                                                    $val      = $activity->{$sig['col']} ?? 'pending';
                                                    $dotColor = match(true) {
                                                        $val === 'approved'      => '#22c55e',
                                                        $val === 'for signature' => '#014ea8',
                                                        $val === 'disapproved'   => '#da281c',
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
                                                    style="width:12px; height:12px; border-radius:50%; background:{{ $dotColor }}; flex-shrink:0; box-shadow:0 0 0 2px {{ $dotColor }}33;">
                                                </div>
                                            @endforeach
                                            <span style="font-size:11px; font-weight:700; color:#64748b; margin-left:4px; white-space:nowrap;">
                                                {{ $approvedCount }}/{{ $totalApprovals }}
                                            </span>
                                        </div>
                                        <div style="margin-top:5px;">
                                            @if($totalApprovals > 0 && $approvedCount === $totalApprovals)
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
                                    <td style="white-space:nowrap;">
                                        @include('partials.sarf-status-badge', ['activity' => $activity])
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <div class="td-main">{{ $activity->created_at?->format('M j, Y') ?? '—' }}</div>
                                        <div class="td-sub">{{ $activity->created_at?->format('g:i A') ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="action-cell">
                                            <a href="{{ route('dean_osa.tracer.show', $activity->id) }}"
                                                class="abtn abtn-view" title="View Activity Tracer">
                                                <i class="fas fa-route"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @empty
            <div style="text-align:center; padding:40px; color:#94a3b8;">
                <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px; color:#e2e8f0;"></i>
                No activities found.
            </div>
        @endforelse

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">
                    Showing {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }}
                    of {{ $activities->total() }} entries
                </span>
                <form method="GET" action="{{ route('dean_osa.tracer.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => 'dean_osa.tracer.index'])
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

<script>
    function toggleBranch(slug) {
        const body    = document.getElementById('branch-' + slug);
        const chevron = document.getElementById('chevron-' + slug);
        const hiding  = body.style.display !== 'none';
        body.style.display   = hiding ? 'none' : '';
        chevron.style.transform = hiding ? 'rotate(180deg)' : '';
    }
</script>
@endsection
