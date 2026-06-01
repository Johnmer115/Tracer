@extends('Dean_OSA.layouts.layout')

@section('title', 'Organization Management | SARF Tracking')
@section('page-title', 'Organization Management')

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
                        <th>Branch</th>
                        <th>Department</th>
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
                            <td class="td-muted">{{ $organization->department->branch->name ?? 'N/A' }}</td>
                            <td class="td-muted">{{ $organization->department->name ?? 'N/A' }}</td>
                            <td class="td-muted">{{ $organization->created_at?->format('m/d/Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="action-cell">

                                    <a href="{{ route('dean_osa.orgs.edit', $organization->id) }}"
                                        class="abtn abtn-edit" title="Edit Organization">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>

                                    <form action="{{ route('dean_osa.orgs.destroy', $organization->id) }}"
                                        method="POST" style="display:inline;"
                                        id="delete-form-{{ $organization->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="abtn abtn-del" title="Delete Organization"
                                            data-delete-id="{{ $organization->id }}"
                                            data-delete-name="{{ $organization->name }}"
                                            onclick="openDeleteModal(this)">
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

<div class="del-overlay" id="deleteModal" aria-hidden="true" onclick="closeDeleteModal()">
    <div class="del-modal" role="dialog" aria-modal="true" aria-labelledby="deleteTitle" onclick="event.stopPropagation()">
        <div class="del-head">
            <i class="fas fa-trash-alt"></i>
            <span id="deleteTitle">Delete organization?</span>
        </div>
        <div class="del-body">
            <p style="margin:0;">This action cannot be undone.</p>
            <div class="del-name" id="deleteName">Organization Name</div>
            <div class="del-warning">
                <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
                <span>Deleting this organization will permanently delete all activities registered under it.</span>
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

        if (name) name.textContent = button.dataset.deleteName || 'Selected organization';
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
