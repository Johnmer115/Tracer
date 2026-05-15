@extends('Dean_OSA.layouts.layout')

@section('title', 'Edit Organization | SARF Tracking')
@section('page-title', 'Edit Organization')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">
        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-pen-to-square"></i> Edit Organization</div>
                    <p class="form-copy">Update the organization details below.</p>
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
                <form action="{{ route('dean_osa.orgs.update', $organization->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Read only fields --}}
                    <div class="form-group">
                        <label class="form-label">Organization Code</label>
                        <input type="text" class="form-control" id="code"
                            value="{{ $organization->code ?? 'N/A' }}" readonly>
                        <input type="hidden" name="code" id="code-hidden" value="{{ $organization->code }}">
                    </div>

                    {{-- Editable fields --}}
                    <div class="form-group">
                        <label class="form-label" for="name">Organization Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $organization->name) }}"
                            placeholder="Enter organization name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="level">Level</label>
                        <select class="form-control" id="level" name="level" required>
                            <option value="">-- Select Level --</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" @selected(old('level', $organization->level) === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.orgs.index') }}">Back</a>
                        <button type="submit" class="btn btn-add">Update Organization</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <script>
        const nameInput  = document.getElementById('name');
        const codeInput  = document.getElementById('code');
        const codeHidden = document.getElementById('code-hidden');
        const deptCode   = "{{ $organization->department?->code ?? '' }}";

        nameInput.addEventListener('input', function () {
            const words = this.value.trim().split(/\s+/).filter(Boolean);
            let orgCode = '';

            if (words.length === 1) {
                orgCode = words[0].slice(0, 4).toUpperCase();
            } else {
                orgCode = words.map(w => w[0].toUpperCase()).join('');
            }

            const full = deptCode ? deptCode + '-' + orgCode : orgCode;
            codeInput.value  = full; // visible
            codeHidden.value = full; // submitted
        });
    </script>
@endsection
