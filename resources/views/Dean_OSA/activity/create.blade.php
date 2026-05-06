@extends('Dean_OSA.layouts.layout')

@section('title', 'New Activity | SARF Tracking')
@section('page-title', 'New Activity')
 
@section('content')
<section class="panel" style="padding: 25px;">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-plus"></i> New SARF Request</div>
            <div class="panel-controls">
                <a href="{{ route('dean_osa.activity.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">

            {{-- Step Indicators --}}
            <div style="display:flex; gap:8px; margin-bottom:28px; flex-wrap:wrap;">
                @foreach([
                    ['1', 'SARF Detail'],
                    ['2', 'Budget'],
                    ['3', 'Attachment File'],
                ] as $i => $step)
                    <div style="flex:1; text-align:center; min-width:120px;">
                        <button type="button" id="step-indicator-{{ $step[0] }}"
                            onclick="showStep({{ $step[0] }})"
                            aria-label="Go to step {{ $step[0] }}: {{ $step[1] }}"
                            style="width:100%; padding:8px; border:0; border-radius:8px; font-size:13px; font-weight:600;
                            background: {{ $i === 0 ? '#3b82f6' : '#f1f5f9' }};
                            color: {{ $i === 0 ? '#fff' : '#64748b' }}; cursor:pointer; font-family:inherit; transition:all .2s;">
                            {{ $step[0] }}. {{ $step[1] }}
                        </button>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('dean_osa.activity.store') }}" method="POST" enctype="multipart/form-data" id="sarf-form">
                @csrf

                {{-- STEP 1: SARF Detail --}}
                <div class="form-step" id="step-1">
                    <p style="font-weight:700; font-size:15px; margin-bottom:16px;">
                        <i class="fas fa-info-circle"></i> SARF Detail
                    </p>

                    {{-- Fields go here --}}

                    <div style="display:flex; justify-content:flex-end; margin-top:24px;">
                        <button type="button" onclick="nextStep(1)" class="btn btn-add">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Budget --}}
                <div class="form-step" id="step-2" style="display:none;">
                    <p style="font-weight:700; font-size:15px; margin-bottom:16px;">
                        <i class="fas fa-coins"></i> Budget
                    </p>

                    {{-- Fields go here --}}

                    <div style="display:flex; justify-content:space-between; margin-top:24px;">
                        <button type="button" onclick="prevStep(2)" class="btn btn-filter">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" onclick="nextStep(2)" class="btn btn-add">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Attachment File --}}
                <div class="form-step" id="step-3" style="display:none;">
                    <p style="font-weight:700; font-size:15px; margin-bottom:16px;">
                        <i class="fas fa-paperclip"></i> Attachment File
                    </p>

                    <p class="td-muted" style="margin: 0 0 16px;">
                        Select the SARF types needed and upload the corresponding PDF file.
                    </p>

                    @foreach(['A0','A1','A2','A3','A4','A5','A6','A7','A8','A10'] as $type)
                        <div style="display:flex; align-items:center; gap:16px; margin-bottom:12px; padding:12px; border:1px solid #e5e7eb; border-radius:8px;">
                            <input type="checkbox" name="types[]" value="{{ $type }}"
                                id="check_{{ $type }}"
                                onchange="toggleFile('{{ $type }}', this.checked)">
                            <label for="check_{{ $type }}" style="min-width:40px; font-weight:600;">{{ $type }}</label>
                            <input type="file" name="file_{{ $type }}" id="file_{{ $type }}"
                                accept=".pdf" style="display:none; flex:1;">
                        </div>
                    @endforeach

                    <div style="display:flex; justify-content:space-between; margin-top:24px;">
                        <button type="button" onclick="prevStep(3)" class="btn btn-filter">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-add">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>

<script>
    const totalSteps = 3;
    let currentStep = 1;

    function nextStep(current) {
        if (!validateStep(current)) return;
        showStep(current + 1);
    }

    function prevStep(current) {
        showStep(current - 1);
    }

    function showStep(step) {
        if (step < 1 || step > totalSteps) return;
        if (step > currentStep && !validateStepsBefore(step)) return;
        displayStep(step);
    }

    function displayStep(step) {
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById('step-' + i).style.display = i === step ? 'block' : 'none';
        }
        currentStep = step;
        updateIndicators(step);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStepsBefore(targetStep) {
        for (let step = 1; step < targetStep; step++) {
            if (!validateStep(step, true)) return false;
        }
        return true;
    }

    function updateIndicators(active) {
        for (let i = 1; i <= totalSteps; i++) {
            const el = document.getElementById('step-indicator-' + i);
            el.setAttribute('aria-current', i === active ? 'step' : 'false');
            if (i === active) {
                el.style.background = '#3b82f6';
                el.style.color      = '#fff';
                el.style.boxShadow  = '0 0 0 3px rgba(59,130,246,.16)';
            } else if (i < active) {
                el.style.background = '#dcfce7';
                el.style.color      = '#16a34a';
                el.style.boxShadow  = 'none';
            } else {
                el.style.background = '#f1f5f9';
                el.style.color      = '#64748b';
                el.style.boxShadow  = 'none';
            }
        }
    }

    function validateStep(step, jumpToInvalid = false) {
        const stepPanel = document.getElementById('step-' + step);
        const required  = stepPanel.querySelectorAll('[required]');
        for (let field of required) {
            if (!field.value) {
                if (jumpToInvalid) displayStep(step);
                field.focus();
                field.style.borderColor = '#dc2626';
                setTimeout(() => field.style.borderColor = '', 2000);
                return false;
            }
        }
        return true;
    }

    function toggleFile(type, show) {
        const fileInput = document.getElementById('file_' + type);
        fileInput.style.display = show ? 'block' : 'none';
        if (!show) fileInput.value = '';
    }
</script>
@endsection