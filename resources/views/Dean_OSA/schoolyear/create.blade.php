@extends('Dean_OSA.layouts.layout')

@section('title', 'Add School Year | SARF Tracking')
@section('page-title', 'Add School Year')

@section('content')
<section>
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
    <div class="form-panel">
        <div class="panel-header">
            <div>
                <div class="panel-title"><i class="fas fa-calendar-plus"></i> Add School Year</div>
                <p class="form-copy">Fill in the details for the new school year.</p>
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
            <form action="{{ route('dean_osa.schoolyear.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">School Year Name</label>
                    <input type="text" class="form-control" name="name"
                        value="{{ old('name') }}" placeholder="e.g. 2025-2026" required>
                </div>

                <input type="hidden" name="code" id="code" value="{{ old('code') }}">

                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date"
                        value="{{ old('start_date') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date"
                        value="{{ old('end_date') }}" required>
                </div>

                <div class="form-actions">
                    <a class="btn btn-filter" href="{{ route('dean_osa.schoolyear.index') }}">Back</a>
                    <button type="submit" class="btn btn-add">Save School Year</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    document.querySelector('[name="name"]').addEventListener('input', function () {
        const parts = this.value.split('-');
        if (parts.length === 2) {
            const y1 = parts[0].trim().slice(-2);
            const y2 = parts[1].trim().slice(-2);
            document.getElementById('code').value = y1 + y2;
        } else {
            document.getElementById('code').value = '';
        }
    });
</script>
@endsection