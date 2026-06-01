@extends('Dean_OSA.layouts.layout')

@section('title', 'System Logs | SARF Tracking')
@section('page-title', 'System Logs')

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-history"></i> System Activity Logs
            </div>
            <form method="GET" action="{{ route('dean_osa.system-logs.index') }}" class="panel-controls">
                <select name="module" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search logs, users, modules">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; padding:16px 20px; border-bottom:1px solid #e5e7eb;">
            <div style="padding:14px; border:1px solid #e5e7eb; border-radius:8px; background:#f8fafc;">
                <div class="td-sub">Showing Logs</div>
                <div style="font-size:22px; font-weight:800; color:#1e293b;">{{ $logs->total() }}</div>
            </div>
            <div style="padding:14px; border:1px solid #bfdbfe; border-radius:8px; background:#eff6ff;">
                <div class="td-sub">Current User</div>
                <div style="font-size:14px; font-weight:800; color:#1d4ed8;">{{ auth()->user()->username }}</div>
            </div>
            <div style="padding:14px; border:1px solid #bbf7d0; border-radius:8px; background:#f0fdf4;">
                <div class="td-sub">Available Modules</div>
                <div style="font-size:22px; font-weight:800; color:#15803d;">{{ $modules->count() }}</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Record</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $moduleName = $log->module ?? 'General';
                            $hash = crc32($moduleName);
                            $hue = abs($hash) % 360;
                            $pillStyle = "background: hsl({$hue}, 85%, 93%); color: hsl({$hue}, 85%, 26%); border: 1px solid hsl({$hue}, 85%, 82%); padding: 4px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; display: inline-block;";

                            $actionText = Str::lower($log->action);
                            $actionMeta = match(true) {
                                Str::contains($actionText, 'delete') => [
                                    'bg' => '#fff1f2',
                                    'color' => '#be123c',
                                    'border' => '#fecdd3',
                                    'icon' => 'fa-trash-alt',
                                ],
                                Str::contains($actionText, ['reschedule', 'rescheduling']) => [
                                    'bg' => '#fef3c7',
                                    'color' => '#92400e',
                                    'border' => '#fbbf24',
                                    'icon' => 'fa-calendar-alt',
                                ],
                                Str::contains($actionText, ['revision', 'modification', 'updated', 'edited', 'toggled']) => [
                                    'bg' => '#dbeafe',
                                    'color' => '#1d4ed8',
                                    'border' => '#93c5fd',
                                    'icon' => 'fa-edit',
                                ],
                                Str::contains($actionText, ['approved', 'uploaded', 'completed', 'created', 'added', 'marked', 'logged']) => [
                                    'bg' => '#dcfce7',
                                    'color' => '#15803d',
                                    'border' => '#86efac',
                                    'icon' => 'fa-check-circle',
                                ],
                                default => [
                                    'bg' => '#f1f5f9',
                                    'color' => '#475569',
                                    'border' => '#cbd5e1',
                                    'icon' => 'fa-circle',
                                ],
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                <div class="td-main">{{ $log->created_at?->format('M j, Y') }}</div>
                                <div class="td-sub">{{ $log->created_at?->format('g:i A') }}</div>
                            </td>
                            <td>
                                <div class="td-main">{{ $log->account->username ?? 'System' }}</div>
                                <div class="td-sub">{{ $log->account->usertype ?? 'Automated' }}</div>
                            </td>
                            <td>
                                <span class="mini-pill" style="{{ $pillStyle }}">{{ $moduleName }}</span>
                            </td>
                            <td>
                                <span style="
                                    display:inline-flex; align-items:center; gap:6px;
                                    font-size:11.5px; font-weight:700;
                                    padding:4px 10px; border-radius:20px;
                                    background:{{ $actionMeta['bg'] }};
                                    color:{{ $actionMeta['color'] }};
                                    border:1px solid {{ $actionMeta['border'] }};">
                                    <i class="fas {{ $actionMeta['icon'] }}" style="font-size:10px;"></i>
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>
                                @if($log->subject_label)
                                    <span class="row-id">{{ $log->subject_label }}</span>
                                @else
                                    <span class="td-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="td-main" style="font-weight:500;">{{ $log->description ?? '-' }}</div>
                            </td>
                            <td style="white-space:nowrap;">
                                <span class="td-muted">{{ $log->ip_address ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="td-muted" style="text-align:center; padding:40px;">
                                <i class="fas fa-folder-open" style="font-size:24px; display:block; margin-bottom:8px; color:#e2e8f0;"></i>
                                No system logs found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">
                    Showing {{ $logs->firstItem() ?? 0 }}&ndash;{{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} entries
                </span>
                <form method="GET" action="{{ route('dean_osa.system-logs.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('module'))
                        <input type="hidden" name="module" value="{{ request('module') }}">
                    @endif
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
                @if($logs->onFirstPage())
                    <span class="pbtn pd">&#8249; Previous</span>
                @else
                    <a class="pbtn" href="{{ $logs->previousPageUrl() }}">&#8249; Previous</a>
                @endif

                @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    @if($page == $logs->currentPage())
                        <span class="pbtn pa">{{ $page }}</span>
                    @else
                        <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($logs->hasMorePages())
                    <a class="pbtn" href="{{ $logs->nextPageUrl() }}">Next &#8250;</a>
                @else
                    <span class="pbtn pd">Next &#8250;</span>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
