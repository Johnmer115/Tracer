@extends('Dean_OSA.layouts.layout')

@section('title', 'Branch Management | SARF Tracking') 
@section('page-title', 'Branch Management') 

@section('content')
    <section class="panel" style="padding: 25px;">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <b>{{ $message }}</b>
                </div>
            @endif
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-users"></i> Branches List</div>
                    <form method="GET" action="{{ route('dean_osa.branch.index') }}" class="panel-controls">
                        <div class="search-wrap">
                            <i class="fas fa-search"></i>
                            <input 
                                class="search-input" 
                                type="text" 
                                name="search"
                                value="{{ request('search', '') }}"
                                placeholder="Search by branch name or code...">
                        </div>
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <a href="{{ route('dean_osa.branch.index') }}" class="btn btn-filter"><i class="fas fa-rotate-left"></i> Reset</a>
                        <a href="{{ route('dean_osa.branch.create') }}" class="btn btn-add"><i class="fas fa-plus"></i> Add Branch</a>
                    </form>
                </div>
                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Code</th>
                                <th>Branch Name</th>
                                <th>Location</th>
                                <th>Created</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr>
                                    <td>
                                        <span class="row-id">
                                            {{ $branch->code ?? '#' . str_pad($branch->id, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="td-name">{{ $branch->name }}</div>
                                    </td>
                                    <td class="td-muted">{{ $branch->location ?? '-' }}</td>
                                    <td class="td-muted">{{ $branch->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="action-cell">
                                          
                                            <!-- EDIT -->
                                            <a href="{{ route('dean_osa.branch.edit', $branch->id) }}" 
                                            class="abtn abtn-edit" 
                                            title="Edit Branch">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>

                                            <!-- DELETE -->
                                            <form action="{{ route('dean_osa.branch.destroy', $branch->id) }}" 
                                                method="POST" 
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="abtn abtn-del" title="Delete Branch">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="td-muted">No accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
 
                <div class="panel-footer">
                    <div class="footer-left">
                        <span class="footer-info">Showing {{ $branches->firstItem() ?? 0 }}-{{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }} entries</span>
                        <form method="GET" action="{{ route('dean_osa.branch.index') }}" class="show-wrap">
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
                        @if($branches->onFirstPage())
                            <span class="pbtn pd">&#8249; Previous</span>
                        @else
                            <a class="pbtn" href="{{ $branches->previousPageUrl() }}">&#8249; Previous</a>
                        @endif
 
                        @foreach($branches->getUrlRange(1, $branches->lastPage()) as $page => $url)
                            @if($page == $branches->currentPage())
                                <span class="pbtn pa">{{ $page }}</span>
                            @else
                                <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
 
                        @if($branches->hasMorePages())
                            <a class="pbtn" href="{{ $branches->nextPageUrl() }}">Next &#8250;</a>
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
