@extends('Dean_OSA.layouts.layout')

@section('title', 'Dashboard | SARF Tracking')
@section('page-title', 'Dashboard')

@push('styles')
<style>
   .dash-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.dash-stat {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    padding: 16px;
    position: relative;
    overflow: hidden;
}
.dash-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 12px 12px 0 0;
}
.dash-stat[data-color="purple"]::before { background: #7F77DD; }
.dash-stat[data-color="amber"]::before  { background: #EF9F27; }
.dash-stat[data-color="blue"]::before   { background: #378ADD; }
.dash-stat[data-color="teal"]::before   { background: #1D9E75; }
.dash-stat[data-color="green"]::before  { background: #639922; }

.dash-stat-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    margin-bottom: 12px;
}
.dash-stat[data-color="purple"] .dash-stat-icon { background: #EEEDFE; color: #534AB7; }
.dash-stat[data-color="amber"]  .dash-stat-icon { background: #FAEEDA; color: #854F0B; }
.dash-stat[data-color="blue"]   .dash-stat-icon { background: #E6F1FB; color: #185FA5; }
.dash-stat[data-color="teal"]   .dash-stat-icon { background: #E1F5EE; color: #0F6E56; }
.dash-stat[data-color="green"]  .dash-stat-icon { background: #EAF3DE; color: #3B6D11; }

.dash-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 4px;
}
.dash-stat-value {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    color: #1e293b;
}
.dash-stat-footer {
    margin-top: 10px;
    font-size: 11px;
    color: #94a3b8;
    display: flex; align-items: center; gap: 5px;
}
.dash-stat-footer .fa-circle { font-size: 6px; }
    .filter-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .35);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
        z-index: 70;
    }
    .filter-backdrop.is-open {
        opacity: 1;
        pointer-events: auto;
    }
    .filter-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: min(390px, 100%);
        height: 100vh;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        box-shadow: -18px 0 40px rgba(15, 23, 42, .16);
        transform: translateX(100%);
        transition: transform .22s ease;
        z-index: 80;
        display: flex;
        flex-direction: column;
    }
    .filter-drawer.is-open { transform: translateX(0); }
    .filter-drawer-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .filter-drawer-title {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .drawer-close {
        border: 0;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .filter-drawer-body {
        padding: 18px 20px;
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #64748b;
        margin-bottom: 7px;
    }
    .filter-checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .filter-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #3b82f6;
    }
    .filter-checkbox-item label {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
        color: #1e293b;
        cursor: pointer;
        display: inline;
    }
    .filter-actions {
        margin-top: auto;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .active-filter-strip {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 0 0 14px;
    }
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 20px;
        padding: 5px 10px;
    }
    @media (max-width: 720px) {
        .dashboard-actions {
            width: 100%;
            justify-content: flex-start !important;
        }
    }
</style>
@endpush

@php
    $activeFilters = collect($filters)->filter(fn($value) => filled($value));
    $selectedLevels = collect((array) ($filters['level'] ?? []))
        ->filter(fn($value) => filled($value))
        ->map(fn($value) => (string) $value)
        ->all();
    $filterDisplayValue = function ($key, $value) use ($branches) {
        if ($key === 'branch_id') {
            return optional($branches->firstWhere('id', (int) $value))->name ?? 'Unknown branch';
        }

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->filter(fn($item) => filled($item))
                ->implode(', ');
        }

        return $value;
    };
    $statusBadge = function($activity) {
        if ($activity->dashboard_inside_status) {
            return ['label' => $activity->dashboard_inside_status, 'class' => 'b-ongoing', 'icon' => 'fa-map-marker-alt'];
        }

        return match($activity->status) {
            'pending' => ['label' => 'Pending', 'class' => 'b-pending', 'icon' => 'fa-clock'],
            'for approval', 'for approval finance' => ['label' => ucfirst($activity->status), 'class' => 'b-ongoing', 'icon' => 'fa-spinner'],
            'approved' => ['label' => 'Approved', 'class' => 'b-approved', 'icon' => 'fa-check-circle'],
            'completed' => ['label' => 'Completed', 'class' => 'b-completed', 'icon' => 'fa-check-double'],
            'for revision' => ['label' => 'For Revision', 'class' => 'b-revision', 'icon' => 'fa-redo'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'b-inactive', 'icon' => 'fa-ban'],
            default => ['label' => ucfirst((string) $activity->status), 'class' => 'b-pending', 'icon' => 'fa-circle'],
        };
    };
@endphp

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel-header" style="margin-bottom:16px;">
        <div>
            <div class="panel-title">
                <i class="fas fa-chart-line"></i> SARF Dashboard
            </div>
            <div class="td-sub" style="margin-top:4px;">Overview of activities by branch, level, pipeline, and approval location.</div>
        </div>
        <div class="dashboard-actions" style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
            @if($activeFilters->isNotEmpty())
                <a href="{{ route('dean_osa.index') }}" class="btn btn-filter">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
            @endif
            <button type="button" class="btn btn-add" onclick="openDashboardFilters()">
                <i class="fas fa-sliders-h"></i> Filter
                @if($activeFilters->isNotEmpty())
                    <span style="margin-left:4px;">({{ $activeFilters->count() }})</span>
                @endif
            </button>
        </div>
    </div>

    @if($activeFilters->isNotEmpty())
        <div class="active-filter-strip">
            @foreach($activeFilters as $key => $value)
                <span class="filter-chip">
                    <i class="fas fa-filter"></i>
                    {{ Str::headline($key) }}: {{ $filterDisplayValue($key, $value) }}
                </span>
            @endforeach
        </div>
    @endif
<div class="dash-grid">
    <div class="dash-stat" data-color="purple">
        <div class="dash-stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="dash-stat-label">Total Activities</div>
        <div class="dash-stat-value">{{ $counts['total'] }}</div>
        <div class="dash-stat-footer"><i class="fas fa-calendar-alt"></i> all time</div>
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
                <i class="fas fa-list"></i> Filtered Activities
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Branch / Level</th>
                        <th>Activity Date</th>
                        <th>Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $badge = $statusBadge($activity);
                            $levelsForRow = is_array($activity->level) ? $activity->level : (filled($activity->level) ? [$activity->level] : []);
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;"><span class="row-id">{{ $activity->code }}</span></td>
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                                <div class="td-sub">{{ $activity->type_of_activity ?? 'Activity' }}</div>
                            </td>
                            <td>
                                <div class="td-main">{{ $activity->branch->name ?? '-' }}</div>
                                <div class="td-sub">{{ count($levelsForRow) ? implode(', ', $levelsForRow) : '-' }}</div>
                            </td>
                            <td style="white-space:nowrap;">
                                <div class="td-main">{{ $activity->date_of_activity?->format('M j, Y') ?? '-' }}</div>
                                <div class="td-sub">{{ $activity->time_of_activity ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $badge['class'] }}">
                                    <i class="fas {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route('dean_osa.tracer.show', $activity->id) }}" class="abtn abtn-view" title="Open Tracer">
                                        <i class="fas fa-route"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="td-muted" style="text-align:center; padding:40px;">
                                No activities match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <span class="footer-info">
                Showing {{ $activities->firstItem() ?? 0 }}&ndash;{{ $activities->lastItem() ?? 0 }}
                of {{ $activities->total() }} entries
            </span>
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

<div id="dashboard-filter-backdrop" class="filter-backdrop" onclick="closeDashboardFilters()"></div>
<aside id="dashboard-filter-drawer" class="filter-drawer" aria-hidden="true">
    <form method="GET" action="{{ route('dean_osa.index') }}" style="display:flex; flex-direction:column; height:100%;">
        <div class="filter-drawer-head">
            <div class="filter-drawer-title">
                <i class="fas fa-sliders-h"></i> Dashboard Filters
            </div>
            <button type="button" class="drawer-close" onclick="closeDashboardFilters()" aria-label="Close filters">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="filter-drawer-body">
            <div class="filter-group">
                <label for="branch_id">Branch</label>
                <select id="branch_id" name="branch_id" class="form-control searchable-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $filters['branch_id'] === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Level</label>
                <div class="filter-checkbox-group">
                    @foreach($levels as $level)
                        <div class="filter-checkbox-item">
                            <input type="checkbox" id="level_{{ $loop->index }}" name="level[]" 
                                value="{{ $level }}" 
                                @checked(in_array((string) $level, $selectedLevels, true))>
                            <label for="level_{{ $loop->index }}">{{ $level }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="filter-group">
                <label for="pipeline_status">Pipeline Status</label>
                <select id="pipeline_status" name="pipeline_status" class="form-control searchable-select">
                    <option value="">All Pipeline Statuses</option>
                    @foreach(['pending' => 'Pending', 'for approval' => 'For Approval', 'approved' => 'Approved', 'completed' => 'Completed'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['pipeline_status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="inside_status">Inside Status</label>
                <select id="inside_status" name="inside_status" class="form-control searchable-select">
                    <option value="">All Inside Statuses</option>
                    @foreach($insideStatuses as $insideStatus)
                        <option value="{{ $insideStatus }}" @selected($filters['inside_status'] === $insideStatus)>{{ $insideStatus }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <a href="{{ route('dean_osa.index') }}" class="btn btn-filter">
                <i class="fas fa-rotate-left"></i> Reset
            </a>
            <button type="submit" class="btn btn-add">
                <i class="fas fa-check"></i> Apply
            </button>
        </div>
    </form>
</aside>
@endsection

@push('scripts')
<script>
function openDashboardFilters() {
    document.getElementById('dashboard-filter-backdrop')?.classList.add('is-open');
    document.getElementById('dashboard-filter-drawer')?.classList.add('is-open');
    document.getElementById('dashboard-filter-drawer')?.setAttribute('aria-hidden', 'false');
}

function closeDashboardFilters() {
    document.getElementById('dashboard-filter-backdrop')?.classList.remove('is-open');
    document.getElementById('dashboard-filter-drawer')?.classList.remove('is-open');
    document.getElementById('dashboard-filter-drawer')?.setAttribute('aria-hidden', 'true');
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeDashboardFilters();
});
</script>
@endpush
