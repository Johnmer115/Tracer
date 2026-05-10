@extends('Dean_OSA.layouts.layout')

@section('title', 'Act for Accomplishment | SARF Tracking')
@section('page-title', 'Act for Accomplishment')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <style>
        .accomplishment-step { display: none; }
        .accomplishment-step.active { display: block; }
        .document-check-row {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 14px 14px;
        }
        .document-check-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #3b82f6;
            background: #dbeafe;
            border-radius: 20px;
            padding: 6px 12px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s;
        }
        .document-check-btn:hover {
            background: #bfdbfe;
            color: #1d4ed8;
        }
        .document-download-btn {
            color: #15803d;
            background: #dcfce7;
        }
        .document-download-btn:hover {
            background: #bbf7d0;
            color: #166534;
        }
        .document-preview-btn {
            display: none;
            color: #7c3aed;
            background: #ede9fe;
        }
        .document-preview-btn.is-visible {
            display: inline-flex;
        }
        .document-preview-btn:hover {
            background: #ddd6fe;
            color: #6d28d9;
        }
        .step-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }
        .step-nav.end { justify-content: flex-end; }
        @media (max-width: 640px) {
            .step-nav {
                flex-direction: column;
                align-items: stretch;
            }
            .step-nav .btn,
            .document-check-btn {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
<section class="panel" style="padding: 25px;">
    @if ($errors->any())
        <div class="alert alert-danger">
            <b>{{ $errors->first() }}</b>
        </div>
    @endif

    <div class="notice-card">
        <i class="fas fa-info-circle"></i>
        <span>
            Upload the accomplishment files for
            <strong>{{ $activity->code }}</strong> - {{ $activity->title }}.
        </span>
    </div>

    <div class="show-section">
        <div class="show-section-header">
            <i class="fas fa-cloud-upload-alt"></i> Act for Accomplishment
        </div>
        <div style="padding:16px 20px;">
            <form action="{{ route('dean_osa.paar.update', $activity->id) }}"
                method="POST" enctype="multipart/form-data"
                style="display:flex; flex-direction:column; gap:16px;">
                @csrf
                @method('PATCH')

                <div class="step-indicators">
                    @foreach($accomplishmentDocuments as $type => $document)
                        @php $stepNumber = $loop->iteration; @endphp
                        <button type="button"
                            id="step-indicator-{{ $stepNumber }}"
                            class="step-indicator-btn {{ $loop->first ? 'active' : '' }}"
                            onclick="showAccomplishmentStep({{ $stepNumber }})">
                            {{ $document['code'] }}. {{ $document['label'] }}
                        </button>
                    @endforeach
                </div>

                <div>
                    @foreach($accomplishmentDocuments as $type => $document)
                        @php
                            $currentDocument = $documents->get($type);
                            $fieldId = $document['field'];
                            $stepNumber = $loop->iteration;
                            $totalSteps = count($accomplishmentDocuments);
                        @endphp
                        <div id="accomplishment-step-{{ $stepNumber }}"
                            class="accomplishment-step {{ $loop->first ? 'active' : '' }}">
                            <div class="approved-upload-card is-selected" id="approved-card-{{ $fieldId }}">
                                <div class="approved-upload-head">
                                    <span class="sarf-badge">{{ $document['code'] }}</span>
                                    <label for="{{ $fieldId }}" class="approved-upload-title">
                                        <strong>{{ $document['label'] }}</strong>
                                        <span>
                                            @if($currentDocument)
                                                Current: {{ $currentDocument->original_filename }}
                                            @else
                                                No file uploaded yet
                                            @endif
                                        </span>
                                    </label>
                                </div>
                                <label class="approved-dropzone" for="{{ $fieldId }}">
                                    <input type="file" name="{{ $fieldId }}" id="{{ $fieldId }}"
                                        accept=".pdf" onchange="updateApprovedFileName('{{ $fieldId }}', this)">
                                    <span class="approved-dropzone-inner">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="approved-dropzone-main">Choose a file or drag and drop it here</span>
                                        <span class="approved-dropzone-sub">PDF format, up to 10MB</span>
                                        <span class="approved-file-chip">
                                            <i class="fas fa-file-pdf"></i>
                                            <span id="approved_fname_{{ $fieldId }}">
                                                {{ $currentDocument ? $currentDocument->original_filename : 'No file chosen' }}
                                            </span>
                                        </span>
                                    </span>
                                </label>
                                <div class="document-check-row">
                                    <a href="#"
                                        target="_blank"
                                        class="document-check-btn document-preview-btn"
                                        id="preview_btn_{{ $fieldId }}">
                                        <i class="fas fa-eye"></i> Preview Selected File
                                    </a>
                                    @if($currentDocument)
                                        <a href="{{ route('dean_osa.sarf-documents.show', $currentDocument) }}"
                                            target="_blank" class="document-check-btn">
                                            <i class="fas fa-file-pdf"></i> View Document
                                        </a>
                                        <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $currentDocument, 'download' => 1]) }}"
                                            class="document-check-btn document-download-btn">
                                            <i class="fas fa-download"></i> Download File
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @error($fieldId)
                                <div class="field-error">{{ $message }}</div>
                            @enderror

                            <div class="step-nav {{ $loop->first ? 'end' : '' }}">
                                @if(! $loop->first)
                                    <button type="button" onclick="showAccomplishmentStep({{ $stepNumber - 1 }})" class="btn btn-filter">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </button>
                                @endif

                                @if(! $loop->last)
                                    <button type="button" onclick="showAccomplishmentStep({{ $stepNumber + 1 }})" class="btn btn-add">
                                        Next <i class="fas fa-arrow-right"></i>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-add"
                                        onclick="return confirm('Complete this accomplishment and mark the activity as completed?');">
                                        <i class="fas fa-check-circle"></i> Complete
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex; justify-content:flex-start;">
                    <a href="{{ route('dean_osa.paar.index') }}" class="btn btn-filter">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function updateApprovedFileName(type, input) {
    const display = document.getElementById('approved_fname_' + type);
    const preview = document.getElementById('preview_btn_' + type);

    if (!input?.files?.length) {
        if (display) display.textContent = 'No file chosen';
        if (preview) {
            preview.classList.remove('is-visible');
            preview.removeAttribute('href');
        }
        return;
    }

    const file = input.files[0];
    if (display) display.textContent = file.name;
    if (preview) {
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);

        const objectUrl = URL.createObjectURL(file);
        preview.href = objectUrl;
        preview.dataset.objectUrl = objectUrl;
        preview.classList.add('is-visible');
    }
}

function showAccomplishmentStep(step) {
    const totalSteps = {{ count($accomplishmentDocuments) }};

    for (let i = 1; i <= totalSteps; i++) {
        const pane = document.getElementById('accomplishment-step-' + i);
        const indicator = document.getElementById('step-indicator-' + i);

        if (pane) pane.classList.toggle('active', i === step);
        if (indicator) {
            indicator.classList.toggle('active', i === step);
            indicator.classList.toggle('completed', i < step);
        }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.approved-dropzone').forEach((dropzone) => {
        const input = dropzone.querySelector('input[type="file"]');
        if (!input) return;

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.style.borderColor = '#3b82f6';
                dropzone.style.background = '#eff6ff';
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.style.background = '#fff';
            });
        });

        dropzone.addEventListener('drop', (event) => {
            if (!event.dataTransfer?.files?.length) return;
            input.files = event.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
});
</script>
@endpush
