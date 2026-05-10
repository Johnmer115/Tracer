@extends('Branch_OSA.dashboard.layout')

@section('title', 'Tracer | SARF Tracking')
@section('page-title', 'Activity Tracer')

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-route"></i> Activity Tracer — {{ $branchName }}</div>
            <form method="GET" action="{{ route('branch_osa.tracer.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input class="search-input" type="text" name="search" value="{{ request('search', '') }}" placeholder="Search title, code, status…">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Funds</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td><span class="row-id">{{ $activity->code }}</span></td>
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                                <div class="td-sub" style="display:flex; gap:5px; flex-wrap:wrap; margin-top:3px;">
                                    @if($activity->type_of_activity)
                                        <span class="mini-pill pill-blue">{{ $activity->type_of_activity }}</span>
                                    @endif
                                    @if($activity->mode_of_conduct)
                                        <span class="mini-pill pill-slate">{{ $activity->mode_of_conduct }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="white-space:nowrap;">
                                <div class="td-main">{{ $activity->date_of_activity?->format('M j, Y') ?? '—' }}</div>
                                <div class="td-sub">{{ $activity->time_of_activity ?? '' }}</div>
                            </td>
                            <td>
                                @if($activity->funds)
                                    @php $fc = match($activity->funds) { 'With Budget'=>'pill-green','ATC'=>'pill-amber',default=>'pill-slate' }; @endphp
                                    <span class="mini-pill {{ $fc }}">{{ $activity->funds }}</span>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sc = match($activity->status) {
                                        'pending'=>'b-pending','for approval','for approval finance'=>'b-for-approval',
                                        'approved'=>'b-approved','completed'=>'b-completed',
                                        'for revision'=>'b-revision',default=>'b-pending',
                                    };
                                @endphp
                                <span class="badge {{ $sc }}">{{ ucfirst($activity->status) }}</span>
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route('branch_osa.tracer.show', $activity->id) }}" class="abtn abtn-view" title="View Tracer"><i class="fas fa-route"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="td-muted" style="text-align:center; padding:40px;">No activities found for your branch.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">Showing {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }}</span>
                <form method="GET" action="{{ route('branch_osa.tracer.index') }}" class="show-wrap">
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    Show
                    <select name="per_page" onchange="this.form.submit()">
                        <option value="10" @selected(request('per_page',10)==10)>10</option>
                        <option value="25" @selected(request('per_page')==25)>25</option>
                        <option value="50" @selected(request('per_page')==50)>50</option>
                    </select>
                    entries
                </form>
            </div>
            <div class="pagi">
                @if($activities->onFirstPage())<span class="pbtn pd">&#8249; Prev</span>@else<a class="pbtn" href="{{ $activities->previousPageUrl() }}">&#8249; Prev</a>@endif
                @foreach($activities->getUrlRange(1, $activities->lastPage()) as $p => $u)
                    @if($p == $activities->currentPage())<span class="pbtn pa">{{ $p }}</span>@else<a class="pbtn" href="{{ $u }}">{{ $p }}</a>@endif
                @endforeach
                @if($activities->hasMorePages())<a class="pbtn" href="{{ $activities->nextPageUrl() }}">Next &#8250;</a>@else<span class="pbtn pd">Next &#8250;</span>@endif
            </div>
        </div>
    </div>
</section>

<style>
.mini-pill { display:inline-block; font-size:11px; font-weight:600; border-radius:20px; padding:2px 8px; white-space:nowrap; }
.pill-blue  { background:#dbeafe; color:#1d4ed8; }
.pill-slate { background:#f1f5f9; color:#475569; }
.pill-green { background:#dcfce7; color:#15803d; }
.pill-amber { background:#fef9c3; color:#92400e; }
</style>
@endsection
