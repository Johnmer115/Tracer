@extends('Dean_OSA.layouts.layout')
 
@section('title', 'Org Activities | SARF Tracking')
@section('page-title', 'Org Activities')
 
@section('content')
<section class="panel" style="padding: 25px;">
     @if ($message = Session::get('success'))
            <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-file-alt"></i> SARF Requests</div>
            <form method="GET" action="{{ route('dean_osa.activity.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input 
                        class="search-input" 
                        type="text" 
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search by Activity Name or Code...">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <a href="{{ route('dean_osa.activity.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> New Activity
                </a>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Activity Name</th>
                        <th>SARF Documents</th>
                        <th>Submitted</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td><span class="row-id">{{ $activity->code }}</span></td>
                            <td><div class="td-name">{{ $activity->name }}</div></td>
                            <td class="td-muted">
                                @foreach($activity->sarfDocuments as $doc)
                                    <span class="badge b-pending">{{ $doc->type }}</span>
                                @endforeach
                            </td>
                            <td class="td-muted">{{ $activity->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="action-cell">
                                    <a href="{{ route('dean_osa.activity.show', $activity->id) }}"
                                        class="abtn abtn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($activity->status === 'pending' || $activity->status === 'for revision')
                                        <a href="{{ route('dean_osa.activity.edit', $activity->id) }}"
                                            class="abtn abtn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('dean_osa.activity.destroy', $activity->id) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('Delete this activity?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="abtn abtn-del" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="td-muted" style="text-align:center;">
                                No activities found. Click <strong>New Activity</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">Showing {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} entries</span>
                <form method="GET" action="{{ route('dean_osa.activity.index') }}" class="show-wrap">
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
