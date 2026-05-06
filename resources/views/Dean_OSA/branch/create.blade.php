@extends('Dean_OSA.layouts.layout')

@section('title', 'Create Branch | SARF Tracking')
@section('page-title', 'Create Branch')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">

        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-code-branch"></i> Add New Branch</div>
                    <p class="form-copy">Fill in the required details below to create a branch.</p>
                </div>
            </div>

            <div class="form-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('dean_osa.branch.store') }}" method="POST">
                    @csrf

                    <div>
                        <div class="form-group">
                            <label class="form-label" for="name">Branch Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Enter branch name"
                                required
                            >
                        </div>

                        <script>
                            document.getElementById('name').addEventListener('input', function () {
                                const words = this.value.trim().split(/\s+/);
                                const code = words
                                    .filter(word => word.length > 0)
                                    .map(word => word[0].toUpperCase())
                                    .join('');
                                document.getElementById('code').value = code;
                            });
                        </script>

                        <div class="form-group">
                            <label class="form-label" for="location">Location</label>
                            <textarea
                                class="form-control"
                                id="location"
                                name="location"
                                rows="4"
                                placeholder="Enter branch location"
                                required
                            >{{ old('location') }}</textarea>
                        </div>

                        <input type="hidden" name="code" id="code" value="{{ old('code') }}">

                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.branch.index') }}">Back</a>
                        <button type="submit" class="btn btn-add">Save Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection