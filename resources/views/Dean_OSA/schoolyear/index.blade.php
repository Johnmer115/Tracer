@extends('Dean_OSA.layouts.layout')

@section('title', 'School Year | SARF Tracking')
@section('page-title', 'School Year Management')

@section('content')
<section class="panel" style="padding: 25px;">
    
    @if ($message = Session::get('success'))
        <div class="alert alert-success"><b>{{ $message }}</b></div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-calendar"></i> School Years</div>
                       <form method="GET" action="{{ route('dean_osa.schoolyear.index') }}" class="panel-controls">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        class="search-input"
                        type="text"
                        name="search"
                        value="{{ request('search', '') }}"
                        placeholder="Search by school year name or code..."
                    >
                </div>
                <select class="filter-select" name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="current" @selected(request('status') === 'current')>Current</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <a href="{{ route('dean_osa.schoolyear.index') }}" class="btn btn-filter"><i class="fas fa-rotate-left"></i> Reset</a>
                <a href="{{ route('dean_osa.schoolyear.create') }}" class="btn btn-add">
                    <i class="fas fa-plus"></i> Add School Year
                </a>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schoolYears as $sy)
                        <tr>
                            <td><div class="td-name">{{ $sy->name }}</div></td>
                            <td><span class="row-id">{{ $sy->code }}</span></td>
                            <td>
                                @if($sy->is_current)
                                    <span class="badge b-active">Current</span>
                                @else
                                    <span class="badge b-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-cell">
                                    @if(!$sy->is_current)
                                        <form action="{{ route('dean_osa.schoolyear.set-current', $sy->id) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('Set {{ $sy->name }} as the current school year?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="abtn abtn-approve" title="Set as Current School Year">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!$sy->is_current)
                                        <form action="{{ route('dean_osa.schoolyear.destroy', $sy->id) }}"
                                            method="POST" style="display:inline;"
                                            id="delete-form-{{ $sy->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="abtn abtn-del" title="Delete School Year"
                                                onclick="confirmDelete('{{ $sy->id }}', '{{ $sy->name }}')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="td-muted" style="text-align:center;">No school years found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <div class="footer-left">
                <span class="footer-info">Showing {{ $schoolYears->firstItem() ?? 0 }}-{{ $schoolYears->lastItem() ?? 0 }} of {{ $schoolYears->total() }} entries</span>
                <form method="GET" action="{{ route('dean_osa.schoolyear.index') }}" class="show-wrap">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
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
                @if($schoolYears->onFirstPage())
                    <span class="pbtn pd">&#8249; Previous</span>
                @else
                    <a class="pbtn" href="{{ $schoolYears->previousPageUrl() }}">&#8249; Previous</a>
                @endif

                @foreach($schoolYears->getUrlRange(1, $schoolYears->lastPage()) as $page => $url)
                    @if($page == $schoolYears->currentPage())
                        <span class="pbtn pa">{{ $page }}</span>
                    @else
                        <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($schoolYears->hasMorePages())
                    <a class="pbtn" href="{{ $schoolYears->nextPageUrl() }}">Next &#8250;</a>
                @else
                    <span class="pbtn pd">Next &#8250;</span>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
    function confirmDelete(id, name) {
        const input = prompt(
            `⚠️ WARNING: You are about to delete school year "${name}".\n\n` +
            `This will permanently delete ALL activities under this school year.\n\n` +
            `Type DELETE to confirm:`
        );

        if (input === 'DELETE') {
            document.getElementById('delete-form-' + id).submit();
        } else {
            alert('Deletion cancelled. You must type DELETE exactly to confirm.');
        }
    }
</script>
@endsection
