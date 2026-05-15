@extends('Dean_OSA.layouts.layout')

@section('title', 'Department Management | SARF Tracking') 
@section('page-title', 'Department Management') 

@section('content')
    <section class="panel" style="padding: 25px;">

            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <b>{{ $message }}</b>
                </div>
            @endif
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-users"></i> Departments List</div>
                    <form method="GET" action="{{ route('dean_osa.department.index') }}" class="panel-controls">
                        <div class="search-wrap">
                            <i class="fas fa-search"></i>
                            <input 
                            class="search-input" 
                            type="text" 
                            name="search"
                            value="{{ request('search', '') }}"
                            placeholder="Search by department name or code...">
                        </div>
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <a href="{{ route('dean_osa.department.index') }}" class="btn btn-filter"><i class="fas fa-rotate-left"></i> Reset</a>
                        <a href="{{ route('dean_osa.department.create') }}" class="btn btn-add"><i class="fas fa-plus"></i> Add Department</a>
                    </form>
                </div>
                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Code</th>
                                <th>Department Name</th>
                                <th>Branch</th>
                                <th>Created</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                                <tr>
                                    <td>
                                        <span class="row-id">
                                            {{ $department->code ?? '#' . str_pad($department->id, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="td-name">{{ $department->name }}</div>
                                    </td>
                                    <td class="td-muted">{{ $department->branch->name ?? '-' }}</td>
                                    <td class="td-muted">{{ $department->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="action-cell">


                                            <!-- EDIT -->
                                            <a href="{{ route('dean_osa.department.edit', $department->id) }}" 
                                            class="abtn abtn-edit" 
                                            title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- DELETE -->
                                            <form action="{{ route('dean_osa.department.destroy', $department->id) }}" 
                                                method="POST" 
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this Department?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="abtn abtn-del" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="td-muted">No departments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
 
                <div class="panel-footer">
                    <div class="footer-left">
                        <span class="footer-info">Showing {{ $departments->firstItem() ?? 0 }}-{{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }} entries</span>
                        <form method="GET" action="{{ route('dean_osa.department.index') }}" class="show-wrap">
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
                        @if($departments->onFirstPage())
                            <span class="pbtn pd">&#8249; Previous</span>
                        @else
                            <a class="pbtn" href="{{ $departments->previousPageUrl() }}">&#8249; Previous</a>
                        @endif
 
                        @foreach($departments->getUrlRange(1, $departments->lastPage()) as $page => $url)
                            @if($page == $departments->currentPage())
                                <span class="pbtn pa">{{ $page }}</span>
                            @else
                                <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
 
                        @if($departments->hasMorePages())
                            <a class="pbtn" href="{{ $departments->nextPageUrl() }}">Next &#8250;</a>
                        @else
                            <span class="pbtn pd">Next &#8250;</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
@endsection
