@extends('Dean_OSA.layouts.layout')

@section('title', 'Organization Management | SARF Tracking')
@section('page-title', 'Organization Management')

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <b>{{ $message }}</b>
        </div>
    @endif
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-users"></i> Organization List</div>
            <form method="GET" action="{{ route('dean_osa.orgs.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input 
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search by organization name or code...">
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <select class="filter-select" name="level" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" @selected(request('level') === $level)>{{ $level }}</option>
                            @endforeach

                </select>
                <a href="{{ route('dean_osa.orgs.index') }}" class="btn btn-filter"><i class="fas fa-rotate-left"></i> Reset</a>
                <a href="{{ route('dean_osa.orgs.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> Add Organization
                </a>
            </form>
        </div>


        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID Code</th>
                        <th>Organization Name</th>
                        <th>Account Name</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Level</th>
                        <th>Created</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <span class="row-id">
                                    {{ $organization->code ?? '#' . str_pad($organization->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td>
                                <div class="td-name">{{ $organization->name }}</div>
                            </td>
                            <td class="td-muted">{{ $organization->account->username ?? 'N/A' }}</td>
                            <td class="td-muted">{{ $organization->department->branch->name ?? 'N/A' }}</td>
                            <td class="td-muted">{{ $organization->department->name ?? 'N/A' }}</td>
                            <td class="td-muted">{{ $organization->level ?? '-' }}</td>
                            <td class="td-muted">{{ $organization->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="action-cell">

                                    <a href="{{ route('dean_osa.orgs.edit', $organization->id) }}"
                                        class="abtn abtn-edit" title="Edit Organization">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>

                                    <form action="{{ route('dean_osa.orgs.destroy', $organization->id) }}"
                                        method="POST" style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this organization? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="abtn abtn-del" title="Delete Organization">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="td-muted">No organizations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">Showing {{ $organizations->firstItem() ?? 0 }}-{{ $organizations->lastItem() ?? 0 }} of {{ $organizations->total() }} entries</span>
                <form method="GET" action="{{ route('dean_osa.orgs.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('level'))
                        <input type="hidden" name="level" value="{{ request('level') }}">
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
                @if($organizations->onFirstPage())
                    <span class="pbtn pd">&#8249; Previous</span>
                @else
                    <a class="pbtn" href="{{ $organizations->previousPageUrl() }}">&#8249; Previous</a>
                @endif

                @foreach($organizations->getUrlRange(1, $organizations->lastPage()) as $page => $url)
                    @if($page == $organizations->currentPage())
                        <span class="pbtn pa">{{ $page }}</span>
                    @else
                        <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($organizations->hasMorePages())
                    <a class="pbtn" href="{{ $organizations->nextPageUrl() }}">Next &#8250;</a>
                @else
                    <span class="pbtn pd">Next &#8250;</span>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
