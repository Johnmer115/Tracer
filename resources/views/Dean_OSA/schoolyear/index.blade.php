@extends('Dean_OSA.layouts.layout')

@section('title', 'School Year | SARF Tracking')
@section('page-title', 'School Year Management')

@push('styles')
<style>
    .sy-delete-overlay{display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}
    .sy-delete-overlay.active{display:flex}
    .sy-delete-modal{width:min(460px,100%);background:#fff;border:1px solid #fecaca;border-radius:8px;box-shadow:0 24px 60px rgba(15,23,42,.24);overflow:hidden}
    .sy-delete-head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #fee2e2;color:#991b1b;font-weight:800}
    .sy-delete-head i{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;color:#dc2626;background:#fee2e2}
    .sy-delete-body{padding:18px 20px;color:#475569;font-size:14px;line-height:1.5}
    .sy-delete-name{margin:12px 0;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#0f172a;font-weight:700}
    .sy-delete-warning{display:flex;gap:10px;margin:14px 0;padding:12px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:13px}
    .sy-delete-input{width:100%;margin-top:8px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-weight:700;letter-spacing:.06em}
    .sy-delete-input:focus{outline:none;border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.12)}
    .sy-delete-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 20px 20px}
    .sy-delete-actions .btn-danger{background:#dc2626;color:#fff;border:1px solid #dc2626}
    .sy-delete-actions .btn-danger:disabled{cursor:not-allowed;opacity:.55}
    @media (max-width:640px){.sy-delete-actions{flex-direction:column-reverse}.sy-delete-actions .btn{justify-content:center}}
</style>
@endpush

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
                        placeholder="Search by school year name or code...">
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
                                            id="set-current-form-{{ $sy->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" class="abtn abtn-approve" title="Set as Current School Year"
                                                data-id="{{ $sy->id }}"
                                                data-name="{{ $sy->name }}"
                                                onclick="openSetCurrentModal(this)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('dean_osa.schoolyear.destroy', $sy->id) }}"
                                            method="POST" style="display:inline;"
                                            id="delete-form-{{ $sy->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="abtn abtn-del" title="Delete School Year"
                                                data-delete-id="{{ $sy->id }}"
                                                data-delete-name="{{ $sy->name }}"
                                                onclick="openSchoolYearDeleteModal(this)">
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
                <span class="footer-info">
                    Showing {{ $schoolYears->firstItem() ?? 0 }}-{{ $schoolYears->lastItem() ?? 0 }}
                    of {{ $schoolYears->total() }} entries
                </span>
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

<div class="sy-delete-overlay" id="schoolYearDeleteModal" aria-hidden="true" onclick="closeSchoolYearDeleteModal()">
    <div class="sy-delete-modal" role="dialog" aria-modal="true" aria-labelledby="schoolYearDeleteTitle" onclick="event.stopPropagation()">
        <div class="sy-delete-head">
            <i class="fas fa-trash-alt"></i>
            <span id="schoolYearDeleteTitle">Delete school year?</span>
        </div>
        <div class="sy-delete-body">
            <p style="margin:0;">This action cannot be undone.</p>
            <div class="sy-delete-name" id="schoolYearDeleteName">School Year</div>
            <div class="sy-delete-warning">
                <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
                <span>Deleting this school year will permanently delete all activities under it.</span>
            </div>
            <label for="schoolYearDeleteInput" style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">
                Type DELETE to confirm
            </label>
            <input id="schoolYearDeleteInput" class="sy-delete-input" type="text" autocomplete="off">
        </div>
        <div class="sy-delete-actions">
            <button type="button" class="btn btn-filter" onclick="closeSchoolYearDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="schoolYearDeleteConfirm" onclick="submitSchoolYearDelete()" disabled>
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
    let selectedSchoolYearDeleteId = null;

    function openSchoolYearDeleteModal(button) {
        selectedSchoolYearDeleteId = button.dataset.deleteId;

        const modal = document.getElementById('schoolYearDeleteModal');
        const name = document.getElementById('schoolYearDeleteName');
        const input = document.getElementById('schoolYearDeleteInput');
        const confirmButton = document.getElementById('schoolYearDeleteConfirm');

        if (name) name.textContent = button.dataset.deleteName || 'Selected school year';
        if (input) input.value = '';
        if (confirmButton) confirmButton.disabled = true;
        if (modal) {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }

        setTimeout(() => input?.focus(), 50);
    }

    function closeSchoolYearDeleteModal() {
        const modal = document.getElementById('schoolYearDeleteModal');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }

        selectedSchoolYearDeleteId = null;
    }

    function submitSchoolYearDelete() {
        if (!selectedSchoolYearDeleteId) return;

        const input = document.getElementById('schoolYearDeleteInput');
        if (input?.value !== 'DELETE') {
            input?.focus();
            return;
        }

        document.getElementById('delete-form-' + selectedSchoolYearDeleteId)?.submit();
    }

    document.getElementById('schoolYearDeleteInput')?.addEventListener('input', (event) => {
        const confirmButton = document.getElementById('schoolYearDeleteConfirm');
        if (confirmButton) confirmButton.disabled = event.target.value !== 'DELETE';
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSchoolYearDeleteModal();
            closeSetCurrentModal();
        }
    });

    let selectedSetCurrentId = null;

    function openSetCurrentModal(button) {
        selectedSetCurrentId = button.dataset.id;
        const modal = document.getElementById('setCurrentModal');
        const name = document.getElementById('setCurrentName');

        if (name) name.textContent = button.dataset.name || 'Selected school year';
        if (modal) {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeSetCurrentModal() {
        const modal = document.getElementById('setCurrentModal');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }
        selectedSetCurrentId = null;
    }

    function submitSetCurrent() {
        if (!selectedSetCurrentId) return;
        document.getElementById('set-current-form-' + selectedSetCurrentId)?.submit();
    }
</script>

<div class="sy-delete-overlay" id="setCurrentModal" aria-hidden="true" onclick="closeSetCurrentModal()">
    <div class="sy-delete-modal" role="dialog" aria-modal="true" aria-labelledby="setCurrentTitle" onclick="event.stopPropagation()" style="border-color: #cbd5e1;">
        <div class="sy-delete-head" style="border-bottom-color: #e2e8f0; color: #1e3a8a;">
            <i class="fas fa-check-circle" style="color: #2563eb; background: #dbeafe;"></i>
            <span id="setCurrentTitle">Activate School Year?</span>
        </div>
        <div class="sy-delete-body">
            <p style="margin:0;">Are you sure you want to set this school year as the current one?</p>
            <div class="sy-delete-name" id="setCurrentName" style="background: #f0f9ff; border-color: #bae6fd; color: #0369a1;">School Year</div>
            <div class="sy-delete-warning" style="background: #f0fdf4; border-color: #bbf7d0; color: #15803d;">
                <i class="fas fa-info-circle" style="color: #16a34a; margin-top:2px;"></i>
                <span>This will set this school year as the default active school year globally.</span>
            </div>
        </div>
        <div class="del-actions" style="display:flex; justify-content:flex-end; gap:10px; padding:16px 20px 20px;">
            <button type="button" class="btn btn-filter" onclick="closeSetCurrentModal()">Cancel</button>
            <button type="button" class="btn btn-add" onclick="submitSetCurrent()" style="background: #2563eb; border-color: #2563eb; color: #fff;">
                <i class="fas fa-check"></i> Set Current
            </button>
        </div>
    </div>
</div>
@endsection
