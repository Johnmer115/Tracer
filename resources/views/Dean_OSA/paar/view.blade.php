@extends('Dean_OSA.layouts.layout')

@section('title', 'View PAAR | SARF Tracking')
@section('page-title', 'View PAAR')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/paar-act.css') }}">
    <style>
        .view-section {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .view-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .view-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .view-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .view-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
        }
        .view-value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 500;
        }
        .document-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px;
        }
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            gap: 12px;
        }
        .document-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .document-actions {
            display: flex;
            gap: 8px;
        }
        .no-doc-badge {
            background-color: #fee2e2;
            color: #ef4444;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-contract"></i> PAAR Details
            </div>
            <div class="panel-controls">
                <div class="sarf-code-display sarf-code-display--header">
                    <span class="code-label">SARF Code</span>
                    <i class="fas fa-hashtag" style="color:#93c5fd; font-size:12px;"></i>
                    <span>{{ $activity->code }}</span>
                </div>
                @include('partials.sarf-status-badge', ['activity' => $activity])
                <a href="{{ route('dean_osa.paar.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">
            <div class="view-section">
                <div class="view-section-header">
                    <i class="fas fa-info-circle"></i> Activity Information
                </div>
                <div class="view-grid">
                    <div class="view-field">
                        <div class="view-label">Activity Title</div>
                        <div class="view-value">{{ $activity->title }}</div>
                    </div>
                    <div class="view-field">
                        <div class="view-label">Branch</div>
                        <div class="view-value">{{ $activity->branch->name ?? '—' }}</div>
                    </div>
                    <div class="view-field">
                        <div class="view-label">Date of Activity</div>
                        <div class="view-value">
                            {{ $activity->date_of_activity
                                ? \Carbon\Carbon::parse($activity->date_of_activity)->format('F d, Y')
                                : '—' }}
                        </div>
                    </div>
                    <div class="view-field">
                        <div class="view-label">Platform / Venue</div>
                        <div class="view-value">
                            @if(in_array($activity->mode_of_conduct, ['Face to Face', 'Hybrid']))
                                {{ $activity->venue ?? '—' }}
                            @else
                                {{ $activity->platform ?? '—' }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <div class="view-section-header">
                    <i class="fas fa-cloud-upload-alt"></i> Accomplishment Documents
                </div>
                <div class="document-list">
                    @foreach($accomplishmentDocuments as $type => $document)
                        @php
                            $currentDocument = $documents->get($type);
                        @endphp
                        <div class="document-item">
                            <div class="document-info">
                                <span class="sarf-badge">{{ $document['code'] }}</span>
                                <div>
                                    <strong style="display:block; color:#1e293b;">{{ $document['label'] }}</strong>
                                    @if($currentDocument)
                                        <span style="font-size: 13px; color:#64748b;">
                                            {{ $currentDocument->original_filename }}
                                        </span>
                                    @else
                                        <span class="no-doc-badge">Not uploaded</span>
                                    @endif
                                </div>
                            </div>
                            @if($currentDocument)
                                <div class="document-actions">
                                    <a href="{{ route('dean_osa.sarf-documents.show', $currentDocument) }}"
                                        target="_blank" class="document-check-btn">
                                        <i class="fas fa-file-pdf"></i> View Document
                                    </a>
                                    <a href="{{ route('dean_osa.sarf-documents.show', ['document' => $currentDocument, 'download' => 1]) }}"
                                        class="document-check-btn document-download-btn">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; justify-content: space-between; margin-top: 20px;">
                <a href="{{ route('dean_osa.paar.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('dean_osa.paar.edit', $activity->id) }}" class="btn btn-add">
                    <i class="fas fa-pencil-alt"></i> Edit PAAR Documents
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
