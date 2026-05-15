@extends('Dean_OSA.layouts.layout')

@section('title', 'Create Department | SARF Tracking')
@section('page-title', 'Create Department')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">
        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-sitemap"></i> Add New Department</div>
                    <p class="form-copy">Fill in the required details below to create a department.</p>
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
                <form action="{{ route('dean_osa.department.store') }}" method="POST">
                    @csrf

                    <div>
                        <div class="form-group">
                            <label class="form-label" for="branch_id">Branch</label>
                            <select class="form-control searchable-select" id="branch_id" name="branch_id" required>
                                <option value="">Select branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                        {{ $branch->name }}{{ $branch->code ? ' (' . $branch->code . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="name">Department Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" placeholder="Enter department name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="3" placeholder="Enter department description">{{ old('description') }}</textarea>
                        </div>

                        <input type="hidden" name="code" id="code" value="{{ old('code') }}">
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.department.index') }}">Back</a>
                        <button type="submit" class="btn btn-add" @disabled($branches->isEmpty())>Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        const branches = {
            @foreach($branches as $branch)
                "{{ $branch->id }}": "{{ $branch->code }}",
            @endforeach
        };

        function generateCode() {
            const branchId   = document.getElementById('branch_id').value;
            const branchCode = branches[branchId] ?? '';
            const name       = document.getElementById('name').value.trim();
            const deptInitials = name.split(/\s+/)
                .filter(w => w.length > 0)
                .map(w => w[0].toUpperCase())
                .join('');
            document.getElementById('code').value = branchCode && deptInitials
                ? branchCode + '-' + deptInitials : '';
        }

        document.getElementById('name').addEventListener('input', generateCode);
        document.getElementById('branch_id').addEventListener('change', generateCode);
    </script>
@endsection
