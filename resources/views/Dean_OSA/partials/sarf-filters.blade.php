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
        'approved'             => 'Approved',
        'completed'            => 'Completed',
        'for revision'         => 'For Revision',
        'cancelled'            => 'Cancelled',
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
        <style>
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
                gap: 6px;
                flex-wrap: wrap;
                align-items: center;
                padding: 8px 14px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                margin-bottom: 14px;
            }
            .active-filter-strip-label {
                font-size: 11px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: .4px;
                margin-right: 2px;
                white-space: nowrap;
            }
            .filter-chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: 11.5px;
                font-weight: 600;
                color: #1d4ed8;
                background: #dbeafe;
                border: 1px solid #bfdbfe;
                border-radius: 20px;
                padding: 3px 10px;
            }
        </style>

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
                <span class="filter-chip">
                    {{ Str::headline($key) }}: {{ $filterDisplayValue($key, $value) }}
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