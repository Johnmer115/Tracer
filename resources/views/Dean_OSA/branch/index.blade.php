@extends('Dean_OSA.layouts.layout')

@section('title', 'Branch Management | SARF Tracking') 
@section('page-title', 'Branch Management') 

@push('styles')
<style>
    .del-overlay{display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}
    .del-overlay.active{display:flex}
    .del-modal{width:min(460px,100%);background:#fff;border:1px solid #fecaca;border-radius:8px;box-shadow:0 24px 60px rgba(15,23,42,.24);overflow:hidden}
    .del-head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #fee2e2;color:#991b1b;font-weight:800}
    .del-head i{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;color:#dc2626;background:#fee2e2}
    .del-body{padding:18px 20px;color:#475569;font-size:14px;line-height:1.5}
    .del-name{margin:12px 0;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#0f172a;font-weight:700}
    .del-warning{display:flex;gap:10px;margin:14px 0;padding:12px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:13px}
    .del-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 20px 20px}
    .del-actions .btn-danger{background:#dc2626;color:#fff;border:1px solid #dc2626}
    @media (max-width:640px){.del-actions{flex-direction:column-reverse}.del-actions .btn{justify-content:center}}
</style>
@endpush

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
                                                id="delete-form-{{ $branch->id }}">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="abtn abtn-del" title="Delete Branch"
                                                    data-delete-id="{{ $branch->id }}"
                                                    data-delete-name="{{ $branch->name }}"
                                                    onclick="openDeleteModal(this)">
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

<div class="del-overlay" id="deleteModal" aria-hidden="true" onclick="closeDeleteModal()">
    <div class="del-modal" role="dialog" aria-modal="true" aria-labelledby="deleteTitle" onclick="event.stopPropagation()">
        <div class="del-head">
            <i class="fas fa-trash-alt"></i>
            <span id="deleteTitle">Delete branch?</span>
        </div>
        <div class="del-body">
            <p style="margin:0;">This action cannot be undone.</p>
            <div class="del-name" id="deleteName">Branch Name</div>
            <div class="del-warning">
                <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
                <span>Deleting this branch will permanently delete all departments, organizations, and activities registered under it.</span>
            </div>
        </div>
        <div class="del-actions">
            <button type="button" class="btn btn-filter" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="submitDelete()">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
    let selectedDeleteId = null;

    function openDeleteModal(button) {
        selectedDeleteId = button.dataset.deleteId;

        const modal = document.getElementById('deleteModal');
        const name = document.getElementById('deleteName');

        if (name) name.textContent = button.dataset.deleteName || 'Selected branch';
        if (modal) {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }
        selectedDeleteId = null;
    }

    function submitDelete() {
        if (!selectedDeleteId) return;
        document.getElementById('delete-form-' + selectedDeleteId)?.submit();
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeDeleteModal();
    });
</script>
@endsection
