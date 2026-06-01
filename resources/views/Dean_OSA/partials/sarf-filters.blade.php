@php
    $filterMode = $filterMode ?? 'panel';
    $filterRoute = $filterRoute ?? request()->route()->getName();
    $filterAction = route($filterRoute);
    $filterId = str_replace(['.', '-'], '_', $filterRoute);
    $filters = $filters ?? [
        'branch_id'    => request('branch_id', ''),
        'level'        => request('level', []),
        'organization' => request('organization', ''),
        'department'   => request('department', ''),
        'pipeline_status' => request('pipeline_status', request('status', '')),
        'inside_status'   => request('inside_status', ''),
    ];

    // Ensure organization / department keys always exist (backwards-compat)
    $filters['organization'] = $filters['organization'] ?? request('organization', '');
    $filters['department']   = $filters['department']   ?? request('department', '');

    $selectedLevels = collect((array) ($filters['level'] ?? []))
        ->filter(fn($value) => filled($value))
        ->map(fn($value) => (string) $value)
        ->all();

    $activeFilters = collect($filters)->filter(fn($value) => filled($value) && $value !== []);

    $pipelineStatuses = $pipelineStatuses ?? [
        'pending'              => 'Pending',
        'ongoing'              => 'Ongoing',
        'for approval'         => 'For Approval',
        'for approval finance' => 'For Approval Finance',
        'rescheduling'         => 'Rescheduling',
        'for approval for rescheduling' => 'For Approval for Rescheduling',
        'approved'             => 'Approved',
        'completed'            => 'Completed',
        'for revision'         => 'For Revision',
        'cancelled'            => 'Cancelled',
    ];
    $pipelineStatusStyles = [
        'pending'              => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'],
        'ongoing'              => ['bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde68a'],
        'for approval'         => ['bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd'],
        'for approval finance' => ['bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd'],
        'rescheduling'         => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fbbf24'],
        'for approval for rescheduling' => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fbbf24'],
        'approved'             => ['bg' => '#dcfce7', 'color' => '#15803d', 'border' => '#86efac'],
        'completed'            => ['bg' => '#f0fdf4', 'color' => '#166534', 'border' => '#4ade80'],
        'for revision'         => ['bg' => '#fff1f2', 'color' => '#da281c', 'border' => '#fca5a5'],
        'for reschedule'       => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fbbf24'],
        'cancelled'            => ['bg' => '#f8fafc', 'color' => '#94a3b8', 'border' => '#e2e8f0'],
    ];

    $filterDisplayValue = function ($key, $value) use ($branches) {
        if ($key === 'branch_id') {
            return optional($branches->firstWhere('id', (int) $value))->name ?? 'Unknown branch';
        }
        if (is_array($value)) {
            return collect($value)->flatten()->filter(fn($item) => filled($item))->implode(', ');
        }
        return $value;
    };
@endphp

@if($filterMode === 'hidden')
    {{-- Preserve all active filters as hidden inputs --}}
    @if(filled($filters['branch_id'] ?? ''))
        <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
    @endif
    @foreach($selectedLevels as $selectedLevel)
        <input type="hidden" name="level[]" value="{{ $selectedLevel }}">
    @endforeach
    @if(filled($filters['organization'] ?? ''))
        <input type="hidden" name="organization" value="{{ $filters['organization'] }}">
    @endif
    @if(filled($filters['department'] ?? ''))
        <input type="hidden" name="department" value="{{ $filters['department'] }}">
    @endif
    @if(filled($filters['pipeline_status'] ?? ''))
        <input type="hidden" name="pipeline_status" value="{{ $filters['pipeline_status'] }}">
    @endif
    @if(filled($filters['inside_status'] ?? ''))
        <input type="hidden" name="inside_status" value="{{ $filters['inside_status'] }}">
    @endif

@elseif($filterMode === 'button')
    <button type="button" class="btn btn-filter" onclick="openSarfFilters('{{ $filterId }}')">
        <i class="fas fa-sliders-h"></i> Filter
        @if($activeFilters->isNotEmpty())
            <span style="margin-left:4px;">({{ $activeFilters->count() }})</span>
        @endif
    </button>

@else
    @once


        <script>
        function openSarfFilters(id) {
            document.getElementById(id + '-filter-backdrop')?.classList.add('is-open');
            document.getElementById(id + '-filter-drawer')?.classList.add('is-open');
            document.getElementById(id + '-filter-drawer')?.setAttribute('aria-hidden', 'false');
        }

        function closeSarfFilters(id) {
            document.getElementById(id + '-filter-backdrop')?.classList.remove('is-open');
            document.getElementById(id + '-filter-drawer')?.classList.remove('is-open');
            document.getElementById(id + '-filter-drawer')?.setAttribute('aria-hidden', 'true');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.filter-drawer.is-open').forEach((drawer) => {
                closeSarfFilters(drawer.id.replace('-filter-drawer', ''));
            });
        });
        </script>
    @endonce



    {{-- Active filter chips row --}}
    @if($activeFilters->isNotEmpty())
        <div class="active-filter-strip">
            <span class="active-filter-strip-label">
                <i class="fas fa-filter" style="margin-right:3px;"></i> Active filters:
            </span>
            @foreach($activeFilters as $key => $value)
                @php
                    $statusStyle = $key === 'pipeline_status'
                        ? ($pipelineStatusStyles[$value] ?? null)
                        : null;
                @endphp
                <span class="filter-chip"
                    @if($statusStyle)
                        style="background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['color'] }}; border-color:{{ $statusStyle['border'] }};"
                    @endif>
                    {{ Str::headline($key) }}:
                    {{ $key === 'pipeline_status' ? ($pipelineStatuses[$value] ?? $filterDisplayValue($key, $value)) : $filterDisplayValue($key, $value) }}
                </span>
            @endforeach
        </div>
    @endif

    <div id="{{ $filterId }}-filter-backdrop" class="filter-backdrop" onclick="closeSarfFilters('{{ $filterId }}')"></div>

    <aside id="{{ $filterId }}-filter-drawer" class="filter-drawer" aria-hidden="true">
        <form method="GET" action="{{ $filterAction }}" style="display:flex; flex-direction:column; height:100%;">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <div class="filter-drawer-head">
                <div class="filter-drawer-title">
                    <i class="fas fa-sliders-h"></i> SARF Filters
                </div>
                <button type="button" class="drawer-close" onclick="closeSarfFilters('{{ $filterId }}')" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="filter-drawer-body">

                {{-- Branch --}}
                <div class="filter-group">
                    <label for="{{ $filterId }}_branch_id">Branch</label>
                    <select id="{{ $filterId }}_branch_id" name="branch_id" class="form-control searchable-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Level --}}
                <div class="filter-group">
                    <label>Level</label>
                    <div class="filter-checkbox-group">
                        @foreach($levels as $level)
                            <div class="filter-checkbox-item">
                                <input type="checkbox"
                                    id="{{ $filterId }}_level_{{ $loop->index }}"
                                    name="level[]"
                                    value="{{ $level }}"
                                    @checked(in_array((string) $level, $selectedLevels, true))>
                                <label for="{{ $filterId }}_level_{{ $loop->index }}">{{ $level }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Organization --}}
                <div class="filter-group">
                    <label for="{{ $filterId }}_organization">Organization</label>
                    <select id="{{ $filterId }}_organization" name="organization" class="form-control searchable-select">
                        <option value="">All Organizations</option>
                        @foreach($allOrganizations ?? [] as $org)
                            <option value="{{ $org }}" @selected(($filters['organization'] ?? '') === $org)>
                                {{ $org }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="filter-group">
                    <label for="{{ $filterId }}_department">Department</label>
                    <select id="{{ $filterId }}_department" name="department" class="form-control searchable-select">
                        <option value="">All Departments</option>
                        @foreach($allDepartments ?? [] as $dept)
                            <option value="{{ $dept }}" @selected(($filters['department'] ?? '') === $dept)>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pipeline Status --}}
                <div class="filter-group">
                    <label for="{{ $filterId }}_pipeline_status">Pipeline Status</label>
                    <select id="{{ $filterId }}_pipeline_status" name="pipeline_status" class="form-control searchable-select">
                        <option value="">All Pipeline Statuses</option>
                        @foreach($pipelineStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['pipeline_status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Inside Status --}}
                <div class="filter-group">
                    <label for="{{ $filterId }}_inside_status">Inside Status</label>
                    <select id="{{ $filterId }}_inside_status" name="inside_status" class="form-control searchable-select">
                        <option value="">All Inside Statuses</option>
                        @foreach($insideStatuses as $insideStatus)
                            <option value="{{ $insideStatus }}" @selected(($filters['inside_status'] ?? '') === $insideStatus)>{{ $insideStatus }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="filter-actions">
                <a href="{{ $filterAction }}" class="btn btn-filter">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
                <button type="submit" class="btn btn-add">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
        </form>
    </aside>
@endif
