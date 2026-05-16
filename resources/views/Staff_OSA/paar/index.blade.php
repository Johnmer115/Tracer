@extends('Staff_OSA.layouts.layout')

@section('title', 'Post-Activity Reports | SARF Tracking')
@section('page-title', 'Post-Activity Reports')

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <b>{{ $errors->first() }}</b>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-medical"></i> Approved and Completed Activities for PAAR
            </div>
            <form method="GET" action="{{ route('dean_osa.paar.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search code or activity">
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
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td style="white-space:nowrap;">
                                <span class="row-id">{{ $activity->code }}</span>
                            </td>
                             {{-- Title + type + mode + event type --}}
                            <td>
                                <div class="td-name">{{ $activity->title }}</div>
                                <div class="td-sub" style="display:flex; gap:5px; flex-wrap:wrap; margin-top:4px;">
                                    @if($activity->type_of_activity)
                                        <span class="mini-pill pill-blue">{{ $activity->type_of_activity }}</span>
                                    @endif
                                    @if($activity->mode_of_conduct)
                                        <span class="mini-pill pill-slate">{{ $activity->mode_of_conduct }}</span>
                                    @endif
                                    @if($activity->event_type)
                                        <span class="mini-pill pill-slate">{{ $activity->event_type }}</span>
                                    @endif
                                </div>
                            </td>

                            
                             {{-- Branch / Level --}}
                            <td class="branch-level-col">
                                <div class="td-main">{{ $activity->branch->name ?? '—' }}</div>
                                @php
                                    $levels = is_array($activity->level) ? $activity->level : [];
                                    $departments = is_array($activity->department)
                                        ? $activity->department
                                        : (filled($activity->department) ? [$activity->department] : []);
                                @endphp
                                @if(count($levels))
                                    <div class="td-sub">{{ implode(', ', $levels) }}</div>
                                @endif
                                @if(count($departments))
                                    <div class="td-sub" >{{implode(', ', $departments)}}</div>
                                @endif
                            </td>

                             {{-- Status --}}
                            <td>
                                <span class="badge {{ $activity->status === 'completed' ? 'b-completed' : 'b-approved' }}">
                                    {{ ucfirst($activity->status) }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                @if($activity->created_at)
                                    <div class="td-main">{{ $activity->created_at->format('M j, Y') }}</div>
                                    <div class="td-sub">{{ $activity->created_at->format('g:i A') }}</div>
                                @else
                                    <span class="td-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route('dean_osa.paar.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View PAAR Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('dean_osa.paar.edit', $activity->id) }}"
                                        class="abtn abtn-edit" title="Edit PAAR">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="{{ route('dean_osa.paar.act', $activity->id) }}"
                                        class="abtn abtn-approve"
                                        title="{{ $activity->status === 'completed' ? 'View Accomplishment Files' : 'Add Accomplishment' }}">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="td-muted" style="text-align:center; padding:40px;">
                                No approved or completed activities ready for Post-Activity Report.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">
                    Showing {{ $activities->firstItem() ?? 0 }}&ndash;{{ $activities->lastItem() ?? 0 }}
                    of {{ $activities->total() }} entries
                </span>
                <form method="GET" action="{{ route('dean_osa.paar.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
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
