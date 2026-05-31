@extends($layout ?? 'Dean_OSA.layouts.layout')

@section('title', 'View PAAR | SARF Tracking')
@section('page-title', 'View PAAR')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/paar-act.css') }}">
    <style>
        .view-section{background:#fff;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .view-section-header{background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px;font-weight:600;color:#1e293b;display:flex;align-items:center;gap:8px;border-top-left-radius:8px;border-top-right-radius:8px}
        .view-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;padding:20px}
        .view-field{display:flex;flex-direction:column;gap:4px}.view-field.full{grid-column:1/-1}
        .view-label{font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:600}
        .view-value{font-size:14px;color:#0f172a;font-weight:500}
        .tag-display{display:flex;flex-wrap:wrap;gap:6px}.tag-display .tag{display:inline-flex;padding:4px 8px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600}
        .document-list{display:flex;flex-direction:column;gap:12px;padding:20px}.document-item{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;gap:12px}.document-info{display:flex;align-items:center;gap:12px}.document-actions{display:flex;gap:8px;flex-wrap:wrap}.no-doc-badge{background:#fee2e2;color:#ef4444;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600}
    </style>
@endpush

@section('content')
<section class="panel" style="padding:25px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-file-contract"></i> {{ $activity->title }}
                <span style="margin-left:6px;">@include('partials.sarf-status-badge', ['activity' => $activity])</span>
            </div>
            <div class="panel-controls">
                <span class="td-muted" style="font-size:12px;">
                    <i class="fas fa-hashtag"></i> {{ $activity->code }}
                    &nbsp;|&nbsp;
                    <i class="fas fa-calendar"></i>
                    {{ filled($activity->date_of_activity) ? \Carbon\Carbon::parse($activity->date_of_activity)->format('M d, Y') : 'N/A' }}
                </span>
                <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding:24px;">
            @php
                $levels = is_array($activity->level) ? $activity->level : (filled($activity->level) ? [$activity->level] : []);
                $depts = is_array($activity->department) ? $activity->department : (filled($activity->department) ? [$activity->department] : []);
                $orgs = is_array($activity->organizations) ? $activity->organizations : (filled($activity->organizations) ? [$activity->organizations] : []);
                $hasPaarInput = collect(array_keys($accomplishmentDocuments))->contains(fn ($type) => $documents->has($type));
                $hasVenue = in_array($activity->mode_of_conduct, ['Face to Face', 'Hybrid'], true);
                $hasPlatform = in_array($activity->mode_of_conduct, ['Online', 'Hybrid'], true);
                $hasBudgetInfo = in_array($activity->funds, ['With Budget', 'ATC'], true);
                $activityDate = filled($activity->date_of_activity)
                    ? \Carbon\Carbon::parse($activity->date_of_activity)->format('M d, Y')
                    : null;
                $detailRows = array_filter([
                    ['Branch', $activity->branch->name ?? null],
                    ['School Year', $activity->school_year_code],
                    ['Date', $activityDate],
                    ['Time', $activity->time_of_activity],
                    $hasVenue ? ['Venue', trim(($activity->venue ?? '') . ($activity->venue_type ? " ({$activity->venue_type})" : ''))] : null,
                    $hasPlatform ? ['Platform', $activity->platform] : null,
                    ['Type', $activity->type_of_activity],
                    ['Mode', $activity->mode_of_conduct],
                    ['Level', count($levels) ? implode(', ', $levels) : null],
                    ['Department', count($depts) ? implode(', ', $depts) : null],
                    ['Organization', count($orgs) ? implode(', ', $orgs) : null],
                    ['Funds', $activity->funds],
                    $hasBudgetInfo && $activity->amount !== null ? ['Requested Budget', 'PHP ' . number_format($activity->amount, 2)] : null,
                    $activity->funds === 'With Budget' ? ['Source', $activity->source] : null,
                    $hasBudgetInfo ? ['Canteen', $activity->canteen] : null,
                    $hasBudgetInfo ? ['Procurement', $activity->procurement] : null,
                    ['Submitted', $activity->created_at?->format('M d, Y g:i A')],
                ], fn ($row) => $row && filled($row[1]));
            @endphp

            <div class="print-details" style="padding:16px;background:#f9fafb;border:1px solid var(--border);border-radius:10px;margin-bottom:20px;">
                <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151;">
                    <i class="fas fa-info-circle"></i> Activity Details
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;">
                    @foreach($detailRows as [$label, $value])
                        <div>
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:3px;">{{ $label }}</div>
                            <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="view-section">
                <div class="view-section-header"><i class="fas fa-cloud-upload-alt"></i> Accomplishment Documents</div>
                <div class="document-list">
                    @foreach($accomplishmentDocuments as $type => $document)
                        @php $currentDocument = $documents->get($type); @endphp
                        <div class="document-item">
                            <div class="document-info">
                                <span class="sarf-badge">{{ $document['code'] }}</span>
                                <div>
                                    <strong style="display:block;color:#1e293b;">{{ $document['label'] }}</strong>
                                    @if($currentDocument)
                                        <span style="font-size:13px;color:#64748b;">{{ $currentDocument->original_filename ?? 'Hardcopy available' }}</span>
                                    @else
                                        <span class="no-doc-badge">Not uploaded</span>
                                    @endif
                                </div>
                            </div>
                            @if($currentDocument?->file_path)
                                <div class="document-actions">
                                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.sarf-documents.show', ['document' => $currentDocument, 'download' => 1]) }}" class="document-check-btn document-download-btn">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            @elseif($currentDocument)
                                <div class="document-actions"><span class="document-check-btn"><i class="fas fa-file-alt"></i> Hardcopy available</span></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($hasPaarInput)
                <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.edit', $activity->id) }}" class="btn btn-add">
                        <i class="fas fa-pencil-alt"></i> Edit PAAR Documents
                    </a>
                </div>
            @else
                <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                    <a href="{{ route(($routePrefix ?? 'dean_osa') . '.paar.act', $activity->id) }}" class="btn btn-add">
                        <i class="fas fa-check-circle"></i> Add Accomplishment
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
