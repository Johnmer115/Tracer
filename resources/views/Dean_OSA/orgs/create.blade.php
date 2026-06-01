@extends('Dean_OSA.layouts.layout')

@section('title', 'Create Organization | SARF Tracking')
@section('page-title', 'Create Organization')

@section('content')
<section>
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
    <div class="form-panel">
        <div class="panel-header">
            <div>
                <div class="panel-title"><i class="fas fa-users"></i> Add New Organization</div>
                <p class="form-copy">Fill in the required details below to create an organization.</p>
            </div>
        </div>
         @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="form-body">
            <form action="{{ route('dean_osa.orgs.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <select class="form-control searchable-select" id="branch_id" name="branch_id" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }} {{ $branch->code ? '(' . $branch->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control searchable-select" id="department_id" name="department_id" required>
                        <option value="">-- Select Branch First --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Organization Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ old('name') }}" placeholder="Enter organization name" required>
                </div>
                

                <input type="hidden" name="code" id="code" value="{{ old('code') }}">
 

                <div class="form-actions">
                    <a class="btn btn-filter" href="{{ route('dean_osa.orgs.index') }}">Back</a>
                    <button type="submit" class="btn btn-add">Save Organization</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // Branch to Department cascade
    document.getElementById('branch_id').addEventListener('change', function () {
        const branchId = this.value;
        const deptSelect = document.getElementById('department_id');
        deptSelect.innerHTML = '<option value="">Loading...</option>';

        if (!branchId) {
            deptSelect.innerHTML = '<option value="">-- Select Branch First --</option>';
            return;
        }

        fetch(`{{ route('dean_osa.department.by-branch') }}?branch_id=${branchId}`)
            .then(res => res.json())
            .then(departments => {
                if (departments.length === 0) {
                    deptSelect.innerHTML = '<option value="">No departments found</option>';
                    return;
                }
                deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
                departments.forEach(dept => {
                    deptSelect.innerHTML += `<option value="${dept.id}" data-code="${dept.code ?? ''}">${dept.name}</option>`;
                });
                const oldDepartmentId = "{{ old('department_id') }}";
                if (oldDepartmentId) {
                    deptSelect.value = oldDepartmentId;
                }
                generateCode();
            });
    });

    function generateCode() {
        const selected = document.getElementById('department_id').selectedOptions[0];
        const deptCode = selected?.dataset.code || '';
        const words = document.getElementById('name').value.trim().split(/\s+/);
        const orgCode = words.filter(w => w.length > 0).map(w => w[0].toUpperCase()).join('');
        document.getElementById('code').value = deptCode && orgCode ? `${deptCode}-${orgCode}` : orgCode;
    }

    document.getElementById('name').addEventListener('input', generateCode);
    document.getElementById('department_id').addEventListener('change', generateCode);
    if (document.getElementById('branch_id').value) {
        document.getElementById('branch_id').dispatchEvent(new Event('change'));
    }
</script>
@endsection
