@extends('Dean_OSA.layouts.layout')

@section('title', 'Edit Department | SARF Tracking')
@section('page-title', 'Edit Department')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">
        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-pen-to-square"></i> Edit Department</div>
                    <p class="form-copy">Update the department details below.</p>
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
                <form action="{{ route('dean_osa.department.update', $department->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="form-group">
                            <label class="form-label">Department Code</label>
                            <input type="text" class="form-control" id="code"
                                value="{{ $department->code }}" readonly>
                            <input type="hidden" name="code" id="code-hidden" value="{{ $department->code }}">
                        </div>

                        {{-- Branch (read only) --}}
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            <input type="text" class="form-control"
                                value="{{ $department->branch?->name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="branch_id" value="{{ $department->branch_id }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="name">Department Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $department->name) }}"
                                placeholder="Enter department name" required>
                        </div>

                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.department.index') }}">Back</a>
                        <button type="submit" class="btn btn-add">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script>
        const nameInput  = document.getElementById('name');
        const codeInput  = document.getElementById('code');
        const codeHidden = document.getElementById('code-hidden');
        const branchCode = "{{ $department->branch?->code ?? '' }}";

        nameInput.addEventListener('input', function () {
            const words = this.value.trim().split(/\s+/).filter(Boolean);
            let deptCode = '';

            if (words.length === 1) {
                deptCode = words[0].slice(0, 4).toUpperCase();
            } else {
                deptCode = words.map(w => w[0].toUpperCase()).join('');
            }

            const full = branchCode ? branchCode + '-' + deptCode : deptCode;
            codeInput.value  = full; // visible
            codeHidden.value = full; // submitted
        });
    </script>
@endsection
