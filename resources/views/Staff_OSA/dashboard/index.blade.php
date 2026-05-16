@extends('Staff_OSA.layouts.layout')

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
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    <div class="panel-header" style="margin-bottom:16px;">
        <div>
            <div class="panel-title">
                <i class="fas fa-chart-line"></i> SARF Dashboard
            </div>
            <div class="td-sub" style="margin-top:4px;">Overview of activities by branch, level, pipeline, and approval location.</div>
        </div>
        <div class="dashboard-actions" style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
            @if($activeFilters->isNotEmpty())
                <a href="{{ route('staff_osa.index') }}" class="btn btn-filter">
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

    {{-- ══════════════════════════════════════════════
         MESSAGE / REMARKS BOARD
    ══════════════════════════════════════════════ --}}
    <div class="msg-board">
        <div class="msg-board-header">
            <div class="msg-board-title">
                <i class="fas fa-comment-dots"></i> Remarks Board
            </div>
            <button type="button" class="btn btn-add" onclick="openComposeModal()">
                <i class="fas fa-plus"></i> New Remark
            </button>
        </div>

        <div class="msg-board-body">
            {{-- Static placeholder cards for template --}}
            @php
                $sampleMessages = [
                    ['type' => 'announcement', 'text' => 'This is a sample announcement message.', 'author' => 'Dean', 'time' => '2 hours ago', 'pinned' => true],
                    ['type' => 'reminder',     'text' => 'This is a sample reminder message.',     'author' => 'Staff',  'time' => '5 hours ago', 'pinned' => false],
                    ['type' => 'general',      'text' => 'This is a sample general remark.',       'author' => 'Branch',   'time' => '1 day ago',   'pinned' => false],
                ];
                $typeConfigs = [
                    'announcement' => ['icon' => 'fa-bullhorn',    'color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'label' => 'Announcement'],
                    'reminder'     => ['icon' => 'fa-bell',        'color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'label' => 'Reminder'],
                    'general'      => ['icon' => 'fa-comment-alt', 'color' => '#014ea8', 'bg' => '#f0f6ff', 'border' => '#93c5fd', 'label' => 'General'],
                ];
            @endphp

            @foreach($sampleMessages as $msg)
                @php $typeConfig = $typeConfigs[$msg['type']]; @endphp
                <div class="msg-card {{ $msg['pinned'] ? 'msg-pinned' : '' }}">
                    <div class="msg-accent" style="background:{{ $typeConfig['color'] }};"></div>
                    <div class="msg-content">
                        <div class="msg-meta">
                            <span class="msg-type-badge" style="background:{{ $typeConfig['bg'] }}; color:{{ $typeConfig['color'] }}; border:1px solid {{ $typeConfig['border'] }};">
                                <i class="fas {{ $typeConfig['icon'] }}" style="font-size:9px;"></i>
                                {{ $typeConfig['label'] }}
                            </span>
                            @if($msg['pinned'])
                                <span class="msg-pin-badge">
                                    <i class="fas fa-thumbtack"></i> Pinned
                                </span>
                            @endif
                            <span class="msg-time">
                                <i class="fas fa-clock" style="font-size:9px;"></i>
                                {{ $msg['time'] }}
                            </span>
                        </div>
                        <div class="msg-text">{{ $msg['text'] }}</div>
                        <div class="msg-footer">
                            <div class="msg-author">
                                <div class="msg-avatar">{{ strtoupper(substr($msg['author'], 0, 1)) }}</div>
                                <span class="msg-author-name">{{ $msg['author'] }}</span>
                            </div>
                            <div class="msg-actions">
                                <button type="button" class="msg-action-btn" title="Pin">
                                    <i class="fas fa-thumbtack {{ $msg['pinned'] ? '' : '' }}" style="{{ $msg['pinned'] ? 'color:var(--primary);' : '' }}"></i>
                                </button>
                                <button type="button" class="msg-action-btn msg-action-del" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         COMPOSE MODAL
    ══════════════════════════════════════════════ --}}
    <div class="compose-overlay" id="composeOverlay" onclick="closeComposeModal()">
        <div class="compose-modal" onclick="event.stopPropagation()">
            <div class="compose-header">
                <div class="compose-header-icon">
                    <i class="fas fa-pen"></i>
                </div>
                <div>
                    <h3 class="compose-title">New Remark</h3>
                    <p class="compose-subtitle">Post a message to the dashboard board</p>
                </div>
                <button type="button" class="compose-close" onclick="closeComposeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="compose-body">
                {{-- Type selector --}}
                <div style="margin-bottom:16px;">
                    <label class="compose-label">Type</label>
                    <div class="compose-type-group">
                        <label class="compose-type-option">
                            <input type="radio" name="type" value="general" checked>
                            <span class="compose-type-chip" style="--chip-bg:#f0f6ff; --chip-color:#014ea8; --chip-border:#93c5fd;">
                                <i class="fas fa-comment-alt"></i> General
                            </span>
                        </label>
                        <label class="compose-type-option">
                            <input type="radio" name="type" value="announcement">
                            <span class="compose-type-chip" style="--chip-bg:#fef2f2; --chip-color:#dc2626; --chip-border:#fca5a5;">
                                <i class="fas fa-bullhorn"></i> Announcement
                            </span>
                        </label>
                        <label class="compose-type-option">
                            <input type="radio" name="type" value="reminder">
                            <span class="compose-type-chip" style="--chip-bg:#fffbeb; --chip-color:#d97706; --chip-border:#fcd34d;">
                                <i class="fas fa-bell"></i> Reminder
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Message input --}}
                <div>
                    <label class="compose-label" for="composeMsg">Message</label>
                    <textarea name="message" id="composeMsg" class="form-control"
                        rows="4" maxlength="2000"
                        placeholder="Write your remark here…"
                        style="resize:vertical; border-radius:10px; font-size:13px;"></textarea>
                    <div style="text-align:right; font-size:10.5px; color:#94a3b8; margin-top:4px;">
                        <span id="charCount">0</span>/2000
                    </div>
                </div>
            </div>

            <div class="compose-footer">
                <button type="button" class="btn btn-filter" onclick="closeComposeModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-add">
                    <i class="fas fa-paper-plane"></i> Post Remark
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         FILTER DRAWER
    ══════════════════════════════════════════════ --}}
    <div id="dashboard-filter-backdrop" class="filter-backdrop" onclick="closeDashboardFilters()"></div>
    <aside id="dashboard-filter-drawer" class="filter-drawer" aria-hidden="true">
        <form method="GET" action="{{ route('staff_osa.index') }}" style="display:flex; flex-direction:column; height:100%;">
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
                <a href="{{ route('staff_osa.index') }}" class="btn btn-filter">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
                <button type="submit" class="btn btn-add">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
        </form>
    </aside>

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

function openComposeModal() {
    document.getElementById('composeOverlay').classList.add('active');
    setTimeout(() => document.getElementById('composeMsg')?.focus(), 200);
}

function closeComposeModal() {
    document.getElementById('composeOverlay').classList.remove('active');
}

// Character counter
document.getElementById('composeMsg')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDashboardFilters();
        closeComposeModal();
    }
});
</script>
@endpush

@endsection