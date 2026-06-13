@extends($layout ?? 'Dean_OSA.layouts.layout')

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
        $requiresLegalApproval = fn($activity) => $activity->waiver_consent === 'With';

        $getApplicableApprovalFields = function($activity) use ($approvalFields, $requiresBasicEdApproval, $requiresFinanceApproval, $requiresLegalApproval) {
            return collect($approvalFields)->filter(function($sig) use ($activity, $requiresBasicEdApproval, $requiresFinanceApproval, $requiresLegalApproval) {
                if ($sig['col'] === 'approval_dir_basic_ed') {
                    return $requiresBasicEdApproval($activity);
                }
                if ($sig['col'] === 'approval_vp_hrd_legal') {
                    return $requiresLegalApproval($activity);
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

            if ($status === 'for approval for rescheduling') {
                return [
                    'label'  => 'Rescheduling',
                    'bg'     => '#fef3c7',
                    'color'  => '#92400e',
                    'border' => '#fbbf24',
                    'icon'   => 'fa-calendar-alt',
                ];
            }

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

    @endphp

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-file-alt"></i> SARF Requests</div>
            <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.tracer.index') }}" class="panel-controls">
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
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden',  'filterRoute' => ($routePrefix ?? 'dean_osa') . '.tracer.index'])
                @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'button',  'filterRoute' => ($routePrefix ?? 'dean_osa') . '.tracer.index'])
                @if (Route::has(($routePrefix ?? 'dean_osa') . '.activity.create'))
                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.activity.create') }}" class="btn btn-add">
                        <i class="fas fa-plus"></i> New Activity
                    </a>
                @endif
            </form>
        </div>

        {{-- Active filter chips + filter drawer --}}
        @include('Dean_OSA.partials.sarf-filters', ['filterRoute' => ($routePrefix ?? 'dean_osa') . '.tracer.index'])

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Branch / Level</th>
                        <th>Activity Date</th>
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
                                <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
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
                                    <div class="td-sub">{{ implode(', ', $levels) }}</div>
                                @endif
                                @if(count($departments))
                                    <div class="td-sub">{{ implode(', ', $departments) }}</div>
                                @endif
                                @if(count($orgs))
                                    <div class="td-sub">{{ implode(', ', $orgs) }}</div>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <div class="td-main">{{ $activity->activityDateDisplay('M j, Y', ', ', 2) ?? '—' }}</div>
                                @if($activity->activityTimeDisplay(', ', 2))
                                    <div class="td-sub">{{ $activity->activityTimeDisplay(', ', 2) }}</div>
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
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.tracer.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View Activity Tracer">
                                        <i class="fas fa-route"></i>
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
                <form method="GET" action="{{ route(($routePrefix ?? 'dean_osa') . '.tracer.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => ($routePrefix ?? 'dean_osa') . '.tracer.index'])
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
