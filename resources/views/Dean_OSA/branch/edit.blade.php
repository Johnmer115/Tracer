@extends('Dean_OSA.layouts.layout')

@section('title', 'Edit Branch | SARF Tracking')
@section('page-title', 'Edit Branch')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">
        
        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-pen-to-square"></i> Edit Branch</div>
                    <p class="form-copy">Update the branch details below.</p>
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

                <form action="{{ route('dean_osa.branch.update', $branch->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Branch Code</label>
                        <input type="text" class="form-control" id="code" name="code"
                            value="{{ old('code', $branch->code) }}" readonly>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="form-label" for="name">Branch Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="{{ old('name', $branch->name) }}"
                                placeholder="Enter branch name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="location">Location</label>
                            <textarea
                                class="form-control"
                                id="location"
                                name="location"
                                rows="4"
                                placeholder="Enter branch location"
                                required
                            >{{ old('location', $branch->location) }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.branch.index') }}">Back</a>
                        <button type="submit" class="btn btn-add">Update Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
<script>
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');

    nameInput.addEventListener('input', function () {
        const words = this.value.trim().split(/\s+/).filter(Boolean);
        let code = '';

        if (words.length === 1) {
            code = words[0].slice(0, 4).toUpperCase();
        } else {
            code = words.map(w => w[0].toUpperCase()).join('');
        }

        codeInput.value = code;
    });
</script>
@endsection

