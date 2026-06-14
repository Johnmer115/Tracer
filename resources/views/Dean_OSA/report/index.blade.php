@extends('Dean_OSA.layouts.layout')

@section('title', 'Report | SARF Tracking')
@section('page-title', 'Report')

@section('content')
@php
    $activeFilters = collect($filters ?? [])->filter(fn($value) => is_array($value) ? count($value) : filled($value));

    $asList = function ($value) {
        return collect(is_array($value) ? $value : (filled($value) ? [$value] : []))
            ->filter(fn($item) => filled($item))
            ->values();
    };

    $morePill = function ($items, $limit = 1) {
        $items = collect($items)->values();
        $visible = $items->take($limit);
        $extra = max($items->count() - $visible->count(), 0);

        return [$visible, $extra];
    };

    $moduleFilters = collect($moduleFilters ?? request()->query('modules', []))->values();
@endphp

<section class="panel" style="padding:25px;">
    <div class="panel-header" style="margin-bottom:16px;">
        <div>
            <div class="panel-title">
                <i class="fas fa-chart-bar"></i> Activity Report
            </div>
            <div class="td-sub" style="margin-top:4px;">Summary and listing of SARF activities.</div>
        </div>
        <form method="GET" action="{{ route('dean_osa.report.index') }}" class="panel-controls">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input
                    class="search-input"
                    type="text"
                    name="search"
                    value="{{ request('search', '') }}"
                    placeholder="Search code or activity">
            </div>
            <div class="report-module-filters" aria-label="Report module filters">
                <label class="report-module-filter">
                    <input
                        type="checkbox"
                        name="modules[]"
                        value="activities"
                        onchange="this.form.submit()"
                        @checked($moduleFilters->contains('activities'))>
                    <span>Activities</span>
                </label>
                <label class="report-module-filter">
                    <input
                        type="checkbox"
                        name="modules[]"
                        value="approvals"
                        onchange="this.form.submit()"
                        @checked($moduleFilters->contains('approvals'))>
                    <span>Approvals</span>
                </label>
                <label class="report-module-filter">
                    <input
                        type="checkbox"
                        name="modules[]"
                        value="paar"
                        onchange="this.form.submit()"
                        @checked($moduleFilters->contains('paar'))>
                    <span>PAAR</span>
                </label>
            </div>
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'hidden', 'filterRoute' => 'dean_osa.report.index'])
            @include('Dean_OSA.partials.sarf-filters', ['filterMode' => 'button', 'filterRoute' => 'dean_osa.report.index'])
        </form>
    </div>

    @include('Dean_OSA.partials.sarf-filters', ['filterRoute' => 'dean_osa.report.index'])

    <div class="dash-grid" style="margin-bottom:18px;">
        <div class="dash-stat" data-color="purple">
            <div class="dash-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="dash-stat-label">Total Activities</div>
            <div class="dash-stat-value">{{ $counts['total'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-calendar-alt"></i> listed records</div>
        </div>
        <div class="dash-stat" data-color="amber">
            <div class="dash-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="dash-stat-label">Pending</div>
            <div class="dash-stat-value">{{ $counts['pending'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle"></i> awaiting action</div>
        </div>
        <div class="dash-stat" data-color="blue">
            <div class="dash-stat-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="dash-stat-label">For Approval</div>
            <div class="dash-stat-value">{{ $counts['for_approval'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle"></i> in pipeline</div>
        </div>
        <div class="dash-stat" data-color="amber">
            <div class="dash-stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="dash-stat-label">Rescheduling</div>
            <div class="dash-stat-value">{{ $counts['rescheduling'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle"></i> schedule changes</div>
        </div>
        <div class="dash-stat" data-color="teal">
            <div class="dash-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="dash-stat-label">Approved</div>
            <div class="dash-stat-value">{{ $counts['approved'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle"></i> cleared</div>
        </div>
        <div class="dash-stat" data-color="green">
            <div class="dash-stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="dash-stat-label">Completed</div>
            <div class="dash-stat-value">{{ $counts['completed'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle"></i> done</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-table"></i> Report Table
            </div>
            <div class="panel-controls">
                <a href="{{ route('dean_osa.report.print', request()->query()) }}" class="btn btn-add" target="_blank" rel="noopener">                    <i class="fas fa-print"></i> Print
                </a>
                @if($activeFilters->isNotEmpty() || filled(request('search')) || $moduleFilters->isNotEmpty())
                    <a href="{{ route('dean_osa.report.index') }}" class="btn btn-filter">
                        <i class="fas fa-rotate-left"></i> Reset
                    </a>
                @endif
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity Name</th>
                        <th>Branch</th>
                        <th>Level</th>
                        <th>Date of Activity</th>
                        <th>Fund's</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $departments = $asList($activity->department);
                            $orgs = $asList($activity->organizations);
                            $levels = $asList($activity->level);
                            $dates = collect($activity->activityDateValues());

                            [$visibleDepartments, $extraDepartments] = $morePill($departments, 1);
                            [$visibleOrgs, $extraOrgs] = $morePill($orgs, 1);
                            [$visibleLevels, $extraLevels] = $morePill($levels, 2);
                            [$visibleDates, $extraDates] = $morePill($dates, 1);

                            $fundsClass = match($activity->funds) {
                                'With Budget' => 'pill-green',
                                'ATC' => 'pill-amber',
                                'No Fee' => 'pill-slate',
                                default => 'pill-slate',
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                <span class="row-id">{{ $activity->code }}</span>
                            </td>
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                            </td>
                            <td>
                                <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
                                <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:5px;">
                                    @foreach($visibleDepartments as $department)
                                        <span class="mini-pill pill-blue">{{ $department }}</span>
                                    @endforeach
                                    @if($extraDepartments > 0)
                                        <span class="mini-pill pill-slate">+{{ $extraDepartments }}</span>
                                    @endif
                                    @foreach($visibleOrgs as $org)
                                        <span class="mini-pill pill-slate">{{ $org }}</span>
                                    @endforeach
                                    @if($extraOrgs > 0)
                                        <span class="mini-pill pill-slate">+{{ $extraOrgs }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px; flex-wrap:wrap;">
                                    @forelse($visibleLevels as $level)
                                        <span class="mini-pill pill-slate">{{ $level }}</span>
                                    @empty
                                        <span class="td-muted">—</span>
                                    @endforelse
                                    @if($extraLevels > 0)
                                        <span class="mini-pill pill-blue">+{{ $extraLevels }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="white-space:nowrap;">
                                @forelse($visibleDates as $date)
                                    <div class="td-main">
                                        <i class="fas fa-calendar-alt" style="color:#94a3b8; font-size:11px;"></i>
                                        {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                                        @if($extraDates > 0)
                                            <span class="mini-pill pill-slate" style="margin-left:4px;">+{{ $extraDates }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="td-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if($activity->funds)
                                    <span class="mini-pill {{ $fundsClass }}">{{ $activity->funds }}</span>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @include('partials.sarf-status-badge', ['activity' => $activity])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="td-muted" style="text-align:center; padding:40px;">
                                No activities found for this report.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                Showing {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }}
                of {{ $activities->total() }} activities
            </div>

            <form method="GET" action="{{ route('dean_osa.report.index') }}" class="show-wrap">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="per_page">Show</label>
                <select name="per_page" id="per_page" onchange="this.form.submit()">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>

            <div class="pagination">
                @if($activities->onFirstPage())
                    <span class="pbtn disabled">Previous</span>
                @else
                    <a class="pbtn" href="{{ $activities->previousPageUrl() }}">Previous</a>
                @endif

                @foreach($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                    @if($page == $activities->currentPage())
                        <span class="pnum active">{{ $page }}</span>
                    @else
                        <a class="pnum" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($activities->hasMorePages())
                    <a class="pbtn" href="{{ $activities->nextPageUrl() }}">Next</a>
                @else
                    <span class="pbtn disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
