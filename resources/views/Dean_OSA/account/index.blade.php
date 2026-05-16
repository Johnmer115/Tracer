@extends('Dean_OSA.layouts.layout')

@section('title', 'Accounts Management | SARF Tracking') 
@section('page-title', 'Accounts Management') 

@section('content')
   <section class="panel" style="padding: 25px;">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <b>{{ $message }}</b>
                </div>
            @endif
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-users"></i> Accounts List</div>
                    <form method="GET" action="{{ route('dean_osa.account.index') }}" class="panel-controls">
                        <div class="search-wrap">
                            <i class="fas fa-search"></i>
                            <input
                                class="search-input"
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search username, org, type..."
                            >
                        </div>
                        <select class="filter-select" name="usertype" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="dean_osa" @selected(request('usertype') === 'dean_osa')>Dean OSA</option>
                            <option value="staff_osa" @selected(request('usertype') === 'staff_osa')>Staff OSA</option>
                            <option value="branch_osa" @selected(request('usertype') === 'branch_osa')>Branch OSA</option>
                        </select>
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <a href="{{ route('dean_osa.account.index') }}" class="btn btn-filter"><i class="fas fa-rotate-left"></i> Reset</a>
                        <a href="{{ route('dean_osa.account.create') }}" class="btn btn-add"><i class="fas fa-plus"></i> Add Account</a>
                    </form>
                </div>


                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Usertype</th>
                                <th>Branch</th>
                                <th>Created</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>
                                        <div class="td-name">{{ $account->username }}</div>
                                    </td>
                                    <td>
                                       <span class="badge 
                                            {{ $account->usertype === 'Dean_OSA' ? 'badge-dean' : '' }}
                                            {{ $account->usertype === 'Staff_OSA' ? 'badge-staff' : '' }}
                                            {{ $account->usertype === 'Branch_OSA' ? 'badge-branch' : '' }}">
                                            
                                            {{ ucfirst(str_replace('_', ' ', $account->usertype)) }}
                                        </span>
                                    </td>
                                    <td class="td-muted">{{ $account->branch?->name ?? '-' }}</td>
                                    <td class="td-muted">{{ $account->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="action-cell">

                                            <!-- EDIT -->
                                            <a href="{{ route('dean_osa.account.edit', $account->id) }}"
                                            class="abtn abtn-edit"
                                            title="Edit Account">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>

                                            <!-- DELETE -->
                                            <form action="{{ route('dean_osa.account.destroy', $account->id) }}"
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="abtn abtn-del" title="Delete Account">
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
                        <span class="footer-info">Showing {{ $accounts->firstItem() ?? 0 }}-{{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} entries</span>
                        <form method="GET" action="{{ route('dean_osa.account.index') }}" class="show-wrap">
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('usertype'))
                                <input type="hidden" name="usertype" value="{{ request('usertype') }}">
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
                        @if($accounts->onFirstPage())
                            <span class="pbtn pd">&#8249; Previous</span>
                        @else
                            <a class="pbtn" href="{{ $accounts->previousPageUrl() }}">&#8249; Previous</a>
                        @endif
 
                        @foreach($accounts->getUrlRange(1, $accounts->lastPage()) as $page => $url)
                            @if($page == $accounts->currentPage())
                                <span class="pbtn pa">{{ $page }}</span>
                            @else
                                <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
 
                        @if($accounts->hasMorePages())
                            <a class="pbtn" href="{{ $accounts->nextPageUrl() }}">Next &#8250;</a>
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