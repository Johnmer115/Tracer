@extends('Dean_OSA.layouts.layout')

@section('title', 'Dashboard | SARF Tracking')
@section('page-title', 'Dashboard')


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
