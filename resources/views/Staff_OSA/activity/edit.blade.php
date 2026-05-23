@extends('Staff_OSA.layouts.layout')

@section('title', 'Edit Activity | SARF Tracking')
@section('page-title', 'Edit Activity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sarf-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sarf-edit.css') }}">
@endpush

@section('content')
<section class="panel" style="padding: 25px;">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-edit"></i> Edit SARF Request</div>
            <div class="panel-controls">
                <div class="sarf-code-display sarf-code-display--header">
                    <span class="code-label">SARF Code</span>
                    <i class="fas fa-hashtag" style="color:#93c5fd; font-size:12px;"></i>
                    <span>{{ $activity->code }}</span>
                </div>
                @include('partials.sarf-status-badge', ['activity' => $activity])
                <a href="{{ route('dean_osa.activity.index') }}" class="btn btn-filter">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div style="padding: 24px;">

            {{-- ── Step Indicators ── --}}
            <div class="step-indicators step-indicators--edit-complete">
                @foreach([
                    ['1', 'Event Details'],
                    ['2', 'Budget'],
                    ['3', 'Attachments'],
                ] as $i => $step)
                    <button type="button"
                        id="step-indicator-{{ $step[0] }}"
                        class="step-indicator-btn completed {{ $i === 0 ? 'active' : '' }}"
                        onclick="showStep({{ $step[0] }})">
                        {{ $step[0] }}. {{ $step[1] }}
                    </button>
                @endforeach
            </div>

            <form action="{{ route('dean_osa.activity.update', $activity->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="sarf-form"
                  novalidate>
                @csrf
                @method('PUT')

                {{-- ══════════════════════════════════════════════
                     STEP 1 — Event Details
                ══════════════════════════════════════════════ --}}
                <div class="form-step" id="step-1">

                    {{-- ── 1-A: Organizational Context ── --}}
                    <div class="context-card" style="margin-bottom:16px;">
                        <p class="context-card-title">
                            <i class="fas fa-sitemap"></i> Organizational Context
                        </p>
                        <div class="form-grid">

                            <div class="form-group">
                                <label class="form-label">Branch <span class="req">*</span></label>
                                <select name="branch_id" class="form-control" required data-label="Branch">
                                    <option value="">— Select Branch —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @selected(old('branch_id', $activity->branch_id) == $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="field-error" id="err-branch_id">Please select a branch.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Level(s) <span class="req">*</span></label>
                                <p class="field-hint">Click a preset or type a custom level and press <kbd>Enter</kbd>.</p>
                                <div class="tag-input-wrap" id="level-wrap">
                                    <div class="tag-list" id="level-tags"></div>
                                    <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px;">
                                        @foreach(['Elementary','Junior High School','Senior High School','College/ETEEAP','Graduate School','All Levels'] as $lvl)
                                            <button type="button" class="level-preset-btn"
                                                onclick="addPresetLevel('{{ $lvl }}')">{{ $lvl }}</button>
                                        @endforeach
                                    </div>
                                    <div class="tag-row">
                                        <input type="text" id="level-input" class="form-control tag-input"
                                            placeholder="Custom level…">
                                        <button type="button" class="btn btn-filter btn-sm"
                                            onclick="addTag('level'); syncLevelPresetButtons()">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <div id="level-hidden"></div>
                                <span class="field-error" id="err-level">Please add at least one level.</span>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Department / Organization(s)</label>
                                <p class="field-hint">Press <kbd>Enter</kbd> or click <strong>Add</strong> after each entry.</p>
                                <div class="tag-input-wrap" id="dept-wrap">
                                    <div class="tag-list" id="dept-tags"></div>
                                    <div class="tag-row">
                                        <input type="text" id="dept-input" class="form-control tag-input"
                                            placeholder="e.g. Computer Science">
                                        <button type="button" class="btn btn-filter btn-sm" onclick="addTag('dept')">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <div id="dept-hidden"></div>
                            </div>

                        </div>
                    </div>

                    {{-- ── 1-B: Activity Core Information ── --}}
                    <div class="context-card context-card--purple" style="margin-bottom:16px;">
                        <p class="context-card-title">
                            <i class="fas fa-calendar-alt"></i> Activity Information
                        </p>
                        <div class="form-grid">

                            <div class="form-group full">
                                <label class="form-label">Activity Title <span class="req">*</span></label>
                                <input type="text" name="title" class="form-control" required
                                    data-label="Activity Title"
                                    placeholder="Enter activity title"
                                    value="{{ old('title', $activity->title) }}">
                                <span class="field-error" id="err-title">Activity title is required.</span>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Short Description</label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="Brief description of the activity">{{ old('description', $activity->description) }}</textarea>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Objectives <span class="req">*</span></label>
                                <p class="field-hint">Press <kbd>Enter</kbd> or click <strong>Add</strong> after each objective.</p>
                                <div class="tag-input-wrap" id="obj-wrap" style="border-radius:8px;">
                                    <ul class="obj-list" id="obj-list"></ul>
                                    <div class="tag-row" style="margin-top:6px;">
                                        <input type="text" id="obj-input" class="form-control tag-input"
                                            placeholder="Enter an objective…">
                                        <button type="button" class="btn btn-filter btn-sm" onclick="addObjective()">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <div id="obj-hidden"></div>
                                <span class="field-error" id="err-objectives">Please add at least one objective.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Type of Activity <span class="req">*</span></label>
                                <select name="type_of_activity" class="form-control" required data-label="Type of Activity">
                                    <option value="">— Select —</option>
                                    <option value="Extra-Curricular"
                                        @selected(old('type_of_activity', $activity->type_of_activity) === 'Extra-Curricular')>Extra-Curricular</option>
                                    <option value="Co-Curricular"
                                        @selected(old('type_of_activity', $activity->type_of_activity) === 'Co-Curricular')>Co-Curricular</option>
                                </select>
                                <span class="field-error" id="err-type_of_activity">Please select the type of activity.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Event Type <span class="req">*</span></label>
                                <select name="event_type" class="form-control" required data-label="Event Type">
                                    <option value="">— Select —</option>
                                    <option value="Internal"
                                        @selected(old('event_type', $activity->event_type) === 'Internal')>Internal</option>
                                    <option value="External"
                                        @selected(old('event_type', $activity->event_type) === 'External')>External</option>
                                </select>
                                <span class="field-error" id="err-event_type">Please select the event type.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Level of Activity <span class="req">*</span></label>
                                <select name="activity_level" class="form-control" required data-label="Level of Activity">
                                    <option value="">— Select —</option>
                                    @foreach(['Organization','Local','Interbranch','Off-Campus'] as $opt)
                                        <option value="{{ $opt }}"
                                            @selected(old('activity_level', $activity->activity_level) === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <span class="field-error" id="err-activity_level">Please select the activity level.</span>
                            </div>

                        </div>
                    </div>

                    {{-- ── 1-C: Schedule, Conduct & Extras ── --}}
                    <div class="context-card context-card--green" style="margin-bottom:16px;">
                        <p class="context-card-title">
                            <i class="fas fa-clock"></i> Schedule, Conduct & Extras
                        </p>
                        <div class="form-grid">

                            <div class="form-group full">
                                <label class="form-label">Mode of Conduct <span class="req">*</span></label>
                                <div class="radio-group" id="mode-radio-group">
                                    @foreach(['Face to Face','Online','Hybrid'] as $mode)
                                        <label class="radio-option">
                                            <input type="radio" name="mode_of_conduct" value="{{ $mode }}"
                                                @checked(old('mode_of_conduct', $activity->mode_of_conduct) === $mode)
                                                onchange="handleMode(this.value)"> {{ $mode }}
                                        </label>
                                    @endforeach
                                </div>
                                <span class="field-error" id="err-mode_of_conduct">Please select a mode of conduct.</span>
                            </div>

                            <div class="form-group full mode-venue" id="venue-block" style="display:none;">
                                <label class="form-label">Venue <span class="req" id="venue-req" style="display:none;">*</span></label>
                                <input type="text" name="venue" class="form-control" id="venue-input"
                                    placeholder="Enter venue name"
                                    value="{{ old('venue', $activity->venue) }}">
                                <div class="radio-group" style="margin-top:10px;">
                                    <label class="radio-option">
                                        <input type="radio" name="venue_type" value="On-Campus"
                                            @checked(old('venue_type', $activity->venue_type ?? '') === 'On-Campus')> On-Campus
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="venue_type" value="Off-Campus"
                                            @checked(old('venue_type', $activity->venue_type ?? '') === 'Off-Campus')> Off-Campus
                                    </label>
                                </div>
                                <span class="field-error" id="err-venue">Venue is required for Face to Face / Hybrid.</span>
                            </div>

                            <div class="form-group mode-platform" id="platform-block" style="display:none;">
                                <label class="form-label">Platform <span class="req" id="platform-req" style="display:none;">*</span></label>
                                @php
                                    $platformOptions = ['Zoom', 'Google Meet', 'Microsoft Teams', 'Facebook Live', 'YouTube Live', 'Google Classroom', 'Moodle', 'Canvas', 'Other'];
                                    $selectedPlatform = old('platform', $activity->platform);
                                @endphp
                                <select name="platform" class="form-control" id="platform-input">
                                    <option value="">— Select Platform —</option>
                                    @foreach($platformOptions as $platform)
                                        <option value="{{ $platform }}" @selected($selectedPlatform === $platform)>{{ $platform }}</option>
                                    @endforeach
                                    @if(filled($selectedPlatform) && !in_array($selectedPlatform, $platformOptions, true))
                                        <option value="{{ $selectedPlatform }}" selected>{{ $selectedPlatform }}</option>
                                    @endif
                                </select>
                                <span class="field-error" id="err-platform">Platform is required for Online / Hybrid.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Date of Activity <span class="req">*</span></label>
                                <input type="date" name="date_of_activity" class="form-control" required
                                    id="date_of_activity" data-label="Date of Activity"
                                    value="{{ old('date_of_activity', $activity->date_of_activity?->format('Y-m-d')) }}"
                                    onchange="checkLateSubmission()">
                                <span class="field-error" id="err-date_of_activity">Date of activity is required.</span>
                            </div>

                            @php
                                $storedTimeRange = (string) old('time_of_activity', $activity->time_of_activity);
                                $storedTimeParts = preg_split('/\s*(?:-|–|—|to)\s*/i', $storedTimeRange);
                                $toTimeInput = function ($value) {
                                    if (! filled($value)) {
                                        return null;
                                    }

                                    $value = str_ireplace('NN', 'PM', trim($value));
                                    $timestamp = strtotime($value);

                                    return $timestamp ? date('H:i', $timestamp) : null;
                                };
                                $storedStartTime = $toTimeInput($storedTimeParts[0] ?? null);
                                $storedEndTime = $toTimeInput($storedTimeParts[1] ?? null);
                            @endphp

                            <div class="form-group">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="time_start" class="form-control"
                                    id="time-start-input"
                                    value="{{ old('time_start', $storedStartTime) }}">
                                <span class="field-error" id="err-time_start">Please enter a start time.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">End Time</label>
                                <input type="time" name="time_end" class="form-control"
                                    id="time-end-input"
                                    value="{{ old('time_end', $storedEndTime) }}">
                                <span class="field-error" id="err-time_end">Please enter an end time after the start time.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Number of Participants</label>
                                <input type="number" name="participants_count" class="form-control" min="0"
                                    placeholder="e.g. 150"
                                    value="{{ old('participants_count', $activity->participants_count) }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Participant Profile</label>
                                <input type="text" name="participants_profile" class="form-control"
                                    placeholder="e.g. All students, Faculty"
                                    value="{{ old('participants_profile', $activity->participants_profile) }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Public Poster</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="public_poster" value="With"
                                            @checked(old('public_poster', $activity->public_poster) === 'With')> With
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="public_poster" value="Without"
                                            @checked(old('public_poster', $activity->public_poster) === 'Without')> Without
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="step-nav end">
                        <button type="button" onclick="nextStep(1)" class="btn btn-add">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>{{-- /step-1 --}}


                {{-- ══════════════════════════════════════════════
                     STEP 2 — Budget
                ══════════════════════════════════════════════ --}}
                <div class="form-step" id="step-2" style="display:none;">
                    <p class="step-section-title"><i class="fas fa-coins"></i> Budgetary Requirements</p>

                    <div class="form-grid">

                        <div class="form-group full">
                            <label class="form-label">Funds <span class="req">*</span></label>
                            <div class="radio-group" id="funds-radio-group">
                                @foreach(['With Budget','ATC','No Fee'] as $fund)
                                    <label class="radio-option">
                                        <input type="radio" name="funds" value="{{ $fund }}"
                                            @checked(old('funds', $activity->funds) === $fund)
                                            onchange="handleFunds(this.value)"> {{ $fund }}
                                    </label>
                                @endforeach
                            </div>
                            <span class="field-error" id="err-funds">Please select a funds option.</span>
                        </div>

                        <div class="form-group" id="source-block" style="display:none;">
                            <label class="form-label">Source <span class="req">*</span></label>
                            <select name="source" class="form-control" id="source-select">
                                <option value="">— Select —</option>
                                @foreach(['SDF','SSC','Guidance','Library Fund','Athletic Fund','Publication Fund','Others'] as $src)
                                    <option value="{{ $src }}"
                                        @selected(old('source', $activity->source) === $src)>{{ $src }}</option>
                                @endforeach
                            </select>
                            <span class="field-error" id="err-source">Please select a budget source.</span>
                        </div>

                        <div class="form-group" id="amount-block" style="display:none;">
                            <label class="form-label">Amount (₱) <span class="req">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                id="amount-input" step="0.01" min="0" placeholder="0.00"
                                value="{{ old('amount', $activity->amount) }}">
                            <span class="field-error" id="err-amount">Please enter the amount.</span>
                        </div>

                        <div class="form-group" id="expected-block" style="display:none;">
                            <label class="form-label">Expected Collection (₱)</label>
                            <input type="number" name="expected_collection" class="form-control"
                                id="expected-collection-input" step="0.01" min="0" placeholder="0.00"
                                value="{{ old('expected_collection', $activity->expected_collection) }}">
                        </div>

                        <div class="form-group" id="canteen-block" style="display:none;">
                            <label class="form-label">Canteen <span class="req">*</span></label>
                            <div class="radio-group" id="canteen-radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="canteen" value="With"
                                        @checked(old('canteen', $activity->canteen) === 'With')> With
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="canteen" value="Without"
                                        @checked(old('canteen', $activity->canteen) === 'Without')> Without
                                </label>
                            </div>
                            <span class="field-error" id="err-canteen">Please select a canteen option.</span>
                        </div>

                        <div class="form-group" id="procurement-block" style="display:none;">
                            <label class="form-label">Procurement <span class="req">*</span></label>
                            <div class="radio-group" id="procurement-radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="procurement" value="With"
                                        @checked(old('procurement', $activity->procurement) === 'With')> With
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="procurement" value="Without"
                                        @checked(old('procurement', $activity->procurement) === 'Without')> Without
                                </label>
                            </div>
                            <span class="field-error" id="err-procurement">Please select a procurement option.</span>
                        </div>

                        <div class="form-group full" id="late-block" style="display:none;">
                            <div class="late-warning-card" id="late-warning-card">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <p class="late-warning-title" id="late-warning-title">Late Submission</p>
                                    <p class="late-warning-desc"  id="late-warning-desc"></p>
                                </div>
                            </div>
                            <label class="form-label" style="margin-top:10px;">
                                Reason for Late Submission <span class="req">*</span>
                            </label>
                            <textarea name="late_submission_reason" class="form-control"
                                id="late-reason-input" rows="3"
                                placeholder="Explain why the SARF is being submitted late…">{{ old('late_submission_reason', $activity->late_submission_reason ?? '') }}</textarea>
                            <span class="field-error" id="err-late_reason">Please provide a reason for late submission.</span>
                        </div>

                    </div>

                    <div class="step-nav">
                        <button type="button" onclick="prevStep(2)" class="btn btn-filter">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" onclick="nextStep(2)" class="btn btn-add">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>{{-- /step-2 --}}


                {{-- ══════════════════════════════════════════════
                     STEP 3 — Attachment Files
                ══════════════════════════════════════════════ --}}
                <div class="form-step" id="step-3" style="display:none;">
                    <p class="step-section-title"><i class="fas fa-paperclip"></i> Attachment Files</p>
                    <p class="td-muted" style="margin: 0 0 16px;">
                        Checked types with an existing file will be kept. Upload a new PDF to replace,
                        or uncheck a type to remove it on save.
                        <strong>At least one attachment is required.</strong>
                    </p>

                    @php
                    $sarfTypes = [
                        'A0'  => 'SARF Form',
                        'A1'  => 'Budget Breakdown',
                        'A2'  => 'Approved Budget Breakdown',
                        'A3'  => 'Program Flow',
                        'A4'  => 'Risk Management Plan',
                        'A5'  => 'Summary List of Waiver / Consent & Medical',
                        'A6'  => 'Reschedule of Activity',
                        'A7'  => 'Acknowledgement Receipt',
                        'A8'  => 'Canteen Slip',
                        'A10' => 'Requested Materials',
                    ];
                    $existingDocs = $activity->sarfDocuments->keyBy('type');
                    @endphp

                    <div id="attachment-list">
                        @foreach($sarfTypes as $type => $label)
                            @php
                                $doc        = $existingDocs->get($type);
                                $hasDoc     = (bool) $doc;
                                $isChecked  = $hasDoc || (is_array(old('types')) && in_array($type, old('types')));
                            @endphp
                            <div class="attachment-row">
                                <label class="attachment-check">
                                    <input type="checkbox" name="types[]" value="{{ $type }}"
                                        id="check_{{ $type }}"
                                        onchange="toggleFile('{{ $type }}', this.checked)"
                                        @checked($isChecked)>
                                    <span class="sarf-badge">{{ $type }}</span>
                                    <span class="sarf-label">{{ $label }}</span>
                                </label>
                                <div class="file-upload-wrap" id="upload-wrap-{{ $type }}"
                                    style="display:{{ $isChecked ? 'flex' : 'none' }};">

                                    @if($hasDoc)
                                        <a href="{{ route('dean_osa.sarf-documents.show', $doc) }}"
                                           target="_blank"
                                           class="existing-doc-link"
                                           title="View existing file">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Existing file</span>
                                            <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
                                        </a>
                                    @endif

                                    <input type="file" name="file_{{ $type }}"
                                        id="file_{{ $type }}" accept=".pdf"
                                        onchange="updateFileName('{{ $type }}', this)">
                                    <label for="file_{{ $type }}" class="file-label">
                                        <i class="fas fa-upload"></i>
                                        {{ $hasDoc ? 'Replace PDF' : 'Choose PDF' }}
                                    </label>
                                    <span class="file-name-display" id="fname_{{ $type }}">No file chosen</span>
                                    <span id="err-file_{{ $type }}"
                                        style="display:none; color:#dc2626; font-size:12px;">
                                        PDF file is required for this type.
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <span id="err-attachments"
                        style="display:none; color:#dc2626; font-size:13px; margin-top:8px;">
                        Please keep at least one attachment type checked.
                    </span>

                    <div class="step-nav">
                        <button type="button" onclick="prevStep(3)" class="btn btn-filter">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" onclick="submitForm()" class="btn btn-add">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>{{-- /step-3 --}}

            </form>
        </div>
    </div>
</section>


<script>
/* ══════════════════════════════════════════════════════════
   SARF Edit Form — JavaScript
   Mirrors create.blade.php; key difference: existing attachments
   don't require a new file upload to pass validation.
══════════════════════════════════════════════════════════ */

/* Types that already have a stored document (no re-upload required) */
const existingDocTypes = @json($activity->sarfDocuments->pluck('type')->values());

/* ── Helpers ── */
function escapeHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/"/g,'&quot;')
        .replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function showError(id, show = true) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = show ? 'block' : 'none';
}
function markInvalid(field, invalid = true) {
    if (!field) return;
    invalid ? field.classList.add('is-invalid') : field.classList.remove('is-invalid');
}
function focusFirstInvalidInStep(step) {
    const stepEl = document.getElementById('step-' + step);
    if (!stepEl) return;

    const visibleError = Array.from(stepEl.querySelectorAll('.field-error'))
        .find(el => el.style.display !== 'none');
    const target = stepEl.querySelector('.is-invalid') ||
        visibleError?.closest('.form-group')?.querySelector('input, select, textarea, button');

    if (!target) return;

    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (typeof target.focus === 'function') {
        target.focus({ preventScroll: true });
    }
}

/* ── Step navigation ── */
const TOTAL_STEPS = 3;
let currentStep   = 1;

function nextStep(current) { if (!validateStep(current)) return; displayStep(current + 1); }
function prevStep(current)  { displayStep(current - 1); }
function showStep(step) {
    if (step > currentStep && !validateStepsBefore(step)) return;
    displayStep(step);
}
function displayStep(step) {
    if (step < 1 || step > TOTAL_STEPS) return;
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const el = document.getElementById('step-' + i);
        if (el) el.style.display = (i === step) ? 'block' : 'none';
    }
    currentStep = step;
    updateIndicators(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function validateStepsBefore(target) {
    for (let s = 1; s < target; s++) if (!validateStep(s, true)) return false;
    return true;
}
function updateIndicators(active) {
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const el = document.getElementById('step-indicator-' + i);
        if (!el) continue;
        el.classList.remove('active');
        el.classList.add('completed');
        if (i === active) el.classList.add('active');
    }
}

/* ── Per-step validation ── */
function validateStep(step, jumpOnFail = false) {
    let valid = true;

    if (step === 1) {
        const branch   = document.querySelector('[name="branch_id"]');
        const branchOk = branch && branch.value.trim() !== '';
        markInvalid(branch, !branchOk); showError('err-branch_id', !branchOk);
        if (!branchOk) valid = false;

        const levelOk = tags['level'].length > 0;
        showError('err-level', !levelOk);
        if (!levelOk) valid = false;

        const title   = document.querySelector('[name="title"]');
        const titleOk = title && title.value.trim() !== '';
        markInvalid(title, !titleOk); showError('err-title', !titleOk);
        if (!titleOk) valid = false;

        const objOk = objectives.length > 0;
        showError('err-objectives', !objOk);
        if (!objOk) valid = false;

        const typeAct   = document.querySelector('[name="type_of_activity"]');
        const typeActOk = typeAct && typeAct.value.trim() !== '';
        markInvalid(typeAct, !typeActOk); showError('err-type_of_activity', !typeActOk);
        if (!typeActOk) valid = false;

        const evType   = document.querySelector('[name="event_type"]');
        const evTypeOk = evType && evType.value.trim() !== '';
        markInvalid(evType, !evTypeOk); showError('err-event_type', !evTypeOk);
        if (!evTypeOk) valid = false;

        const actLevel   = document.querySelector('[name="activity_level"]');
        const actLevelOk = actLevel && actLevel.value.trim() !== '';
        markInvalid(actLevel, !actLevelOk); showError('err-activity_level', !actLevelOk);
        if (!actLevelOk) valid = false;

        const dateAct   = document.querySelector('[name="date_of_activity"]');
        const dateActOk = dateAct && dateAct.value.trim() !== '';
        markInvalid(dateAct, !dateActOk); showError('err-date_of_activity', !dateActOk);
        if (!dateActOk) valid = false;

        const timeStart = document.querySelector('[name="time_start"]');
        const timeEnd = document.querySelector('[name="time_end"]');
        const hasTimeStart = timeStart && timeStart.value.trim() !== '';
        const hasTimeEnd = timeEnd && timeEnd.value.trim() !== '';
        const timeRangeOk = (!hasTimeStart && !hasTimeEnd) || (hasTimeStart && hasTimeEnd && timeEnd.value > timeStart.value);
        markInvalid(timeStart, !timeRangeOk && !hasTimeStart);
        markInvalid(timeEnd, !timeRangeOk && (!hasTimeEnd || timeEnd.value <= timeStart.value));
        showError('err-time_start', hasTimeEnd && !hasTimeStart);
        showError('err-time_end', hasTimeStart && (!hasTimeEnd || timeEnd.value <= timeStart.value));
        if (!timeRangeOk) valid = false;

        const modeChecked = document.querySelector('[name="mode_of_conduct"]:checked');
        const modeOk      = !!modeChecked;
        showError('err-mode_of_conduct', !modeOk);
        if (!modeOk) valid = false;

        if (modeChecked && (modeChecked.value === 'Face to Face' || modeChecked.value === 'Hybrid')) {
            const venueIn = document.getElementById('venue-input');
            const venueOk = venueIn && venueIn.value.trim() !== '';
            markInvalid(venueIn, !venueOk); showError('err-venue', !venueOk);
            if (!venueOk) valid = false;
        } else { showError('err-venue', false); }

        if (modeChecked && (modeChecked.value === 'Online' || modeChecked.value === 'Hybrid')) {
            const platIn = document.getElementById('platform-input');
            const platOk = platIn && platIn.value.trim() !== '';
            markInvalid(platIn, !platOk); showError('err-platform', !platOk);
            if (!platOk) valid = false;
        } else { showError('err-platform', false); }
    }

    if (step === 2) {
        const fundsChecked = document.querySelector('[name="funds"]:checked');
        const fundsOk      = !!fundsChecked;
        showError('err-funds', !fundsOk);
        if (!fundsOk) valid = false;

        if (fundsChecked) {
            const fundsVal = fundsChecked.value;
            if (fundsVal === 'With Budget') {
                const src   = document.getElementById('source-select');
                const srcOk = src && src.value.trim() !== '';
                markInvalid(src, !srcOk); showError('err-source', !srcOk);
                if (!srcOk) valid = false;
            }
            if (fundsVal === 'With Budget' || fundsVal === 'ATC') {
                const amt   = document.getElementById('amount-input');
                const amtOk = amt && amt.value.trim() !== '' && parseFloat(amt.value) >= 0;
                markInvalid(amt, !amtOk); showError('err-amount', !amtOk);
                if (!amtOk) valid = false;
            }
            if (fundsVal === 'With Budget' || fundsVal === 'ATC') {
                const canteenOk = !!document.querySelector('[name="canteen"]:checked');
                const procureOk = !!document.querySelector('[name="procurement"]:checked');
                showError('err-canteen',     !canteenOk);
                showError('err-procurement', !procureOk);
                if (!canteenOk) valid = false;
                if (!procureOk) valid = false;
            }
        }

        const lateBlock = document.getElementById('late-block');
        if (lateBlock && lateBlock.style.display !== 'none') {
            const lateReason = document.getElementById('late-reason-input');
            const lateOk     = lateReason && lateReason.value.trim() !== '';
            markInvalid(lateReason, !lateOk); showError('err-late_reason', !lateOk);
            if (!lateOk) valid = false;
        }
    }

    if (step === 3) {
        const checkedBoxes = document.querySelectorAll('[name="types[]"]:checked');
        const attachOk     = checkedBoxes.length > 0;
        showError('err-attachments', !attachOk);
        if (!attachOk) valid = false;

        checkedBoxes.forEach(cb => {
            const type    = cb.value;
            const fileIn  = document.getElementById('file_' + type);
            /* Existing docs don't need a new upload */
            const fileOk  = existingDocTypes.includes(type) || (fileIn && fileIn.files.length > 0);
            const errEl   = document.getElementById('err-file_' + type);
            if (errEl) errEl.style.display = fileOk ? 'none' : 'inline';
            if (!fileOk) valid = false;
        });
    }

    if (!valid) {
        if (jumpOnFail) displayStep(step);
        setTimeout(() => focusFirstInvalidInStep(step), jumpOnFail ? 250 : 0);
    }
    return valid;
}

function submitForm() {
    if (!validateStep(3)) return;
    document.getElementById('sarf-form').submit();
}

/* ── Mode of Conduct ── */
function handleMode(val) {
    const venueBlock    = document.getElementById('venue-block');
    const platformBlock = document.getElementById('platform-block');
    const venueReq      = document.getElementById('venue-req');
    const platformReq   = document.getElementById('platform-req');

    const showVenue    = (val === 'Face to Face' || val === 'Hybrid');
    const showPlatform = (val === 'Online'       || val === 'Hybrid');

    venueBlock.style.display    = showVenue    ? 'flex' : 'none';
    platformBlock.style.display = showPlatform ? 'flex' : 'none';
    if (venueReq)    venueReq.style.display    = showVenue    ? 'inline' : 'none';
    if (platformReq) platformReq.style.display = showPlatform ? 'inline' : 'none';

    if (!showVenue) {
        const v = document.querySelector('[name="venue"]');
        if (v) v.value = '';
        document.querySelectorAll('[name="venue_type"]').forEach(r => r.checked = false);
        showError('err-venue', false);
    }
    if (!showPlatform) {
        const p = document.querySelector('[name="platform"]');
        if (p) p.value = '';
        showError('err-platform', false);
    }
}

/* ── Funds ── */
let currentFunds = '';
function handleFunds(val) {
    currentFunds = val;
    const show = (id, visible) => {
        const el = document.getElementById(id);
        if (el) el.style.display = visible ? 'flex' : 'none';
    };
    const withBudget = val === 'With Budget';
    const atc        = val === 'ATC';
    const hasBudget  = withBudget || atc;

    show('source-block',      withBudget);
    show('amount-block',      hasBudget);
    show('expected-block',    atc);
    show('canteen-block',     hasBudget);
    show('procurement-block', hasBudget);

    if (!withBudget) { const s = document.getElementById('source-select'); if (s) s.value = ''; showError('err-source', false); }
    if (!hasBudget) {
        const a = document.getElementById('amount-input'); if (a) a.value = '';
        showError('err-amount', false);
    }
    if (!atc) {
        const e = document.getElementById('expected-collection-input'); if (e) e.value = '';
    }
    if (!hasBudget) {
        document.querySelectorAll('[name="canteen"],[name="procurement"]').forEach(r => r.checked = false);
        showError('err-canteen', false); showError('err-procurement', false);
    }
    checkLateSubmission();
}

/* ── Late Submission ── */
function checkLateSubmission() {
    const dateVal   = document.getElementById('date_of_activity')?.value;
    const funds     = currentFunds || (document.querySelector('[name="funds"]:checked')?.value ?? '');
    const lateBlock = document.getElementById('late-block');
    if (!dateVal || !funds) { if (lateBlock) lateBlock.style.display = 'none'; return; }

    const activityDate = new Date(dateVal);
    const today        = new Date(); today.setHours(0,0,0,0);
    const limitDays    = (funds === 'No Fee') ? 15 : 30;
    const deadline     = new Date(activityDate); deadline.setDate(deadline.getDate() - limitDays);
    const isLate       = today > deadline;

    if (isLate) {
        const fmt = { year:'numeric', month:'long', day:'numeric' };
        document.getElementById('late-warning-title').textContent = '⚠ Late Submission Detected';
        document.getElementById('late-warning-desc').textContent  =
            `For a "${funds}" activity on ${activityDate.toLocaleDateString('en-PH', fmt)}, ` +
            `the SARF should have been submitted by ${deadline.toLocaleDateString('en-PH', fmt)} ` +
            `(${limitDays} days prior). Please provide a reason below.`;
        lateBlock.style.display = 'flex';
    } else { if (lateBlock) lateBlock.style.display = 'none'; }
}

/* ── Attachments ── */
function toggleFile(type, show) {
    const wrap  = document.getElementById('upload-wrap-' + type);
    const fileIn = document.getElementById('file_' + type);
    const errEl  = document.getElementById('err-file_' + type);
    if (wrap) wrap.style.display = show ? 'flex' : 'none';
    if (!show && fileIn) {
        fileIn.value = '';
        const fname = document.getElementById('fname_' + type);
        if (fname) fname.textContent = 'No file chosen';
        if (errEl) errEl.style.display = 'none';
    }
    showError('err-attachments', false);
}
function updateFileName(type, input) {
    const fname = document.getElementById('fname_' + type);
    if (fname) fname.textContent = input.files.length ? input.files[0].name : 'No file chosen';
    const errEl = document.getElementById('err-file_' + type);
    if (errEl) errEl.style.display = (input.files.length > 0 || existingDocTypes.includes(type)) ? 'none' : 'inline';
}

/* ── Tag inputs ── */
const tags = { dept: [], level: [] };

function addTag(key) {
    const input = document.getElementById(key + '-input');
    const val   = input?.value.trim();
    if (!val || tags[key].includes(val)) { input?.focus(); return; }
    tags[key].push(val); input.value = ''; renderTags(key); input.focus();
    if (key === 'level') showError('err-level', false);
}
function removeTag(key, val) {
    tags[key] = tags[key].filter(t => t !== val); renderTags(key);
    if (key === 'level') { syncLevelPresetButtons(); showError('err-level', tags['level'].length === 0); }
}
function renderTags(key) {
    const list   = document.getElementById(key + '-tags');
    const hidden = document.getElementById(key + '-hidden');
    const name   = key === 'dept' ? 'department[]' : 'level[]';
    if (list)   list.innerHTML   = tags[key].map(t =>
        `<span class="tag">${escapeHtml(t)}<button type="button" class="tag-remove"
            onclick="removeTag('${key}','${escapeHtml(t)}')">&times;</button></span>`
    ).join('');
    if (hidden) hidden.innerHTML = tags[key].map(t =>
        `<input type="hidden" name="${name}" value="${escapeHtml(t)}">`
    ).join('');
}
function addPresetLevel(val) {
    if (tags['level'].includes(val)) removeTag('level', val);
    else { tags['level'].push(val); renderTags('level'); syncLevelPresetButtons(); showError('err-level', false); }
}
function syncLevelPresetButtons() {
    document.querySelectorAll('.level-preset-btn').forEach(btn =>
        btn.classList.toggle('active', tags['level'].includes(btn.textContent.trim())));
}
document.getElementById('dept-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); addTag('dept'); }
});
document.getElementById('level-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); addTag('level'); syncLevelPresetButtons(); }
});

/* ── Objectives ── */
const objectives = [];
function addObjective() {
    const input = document.getElementById('obj-input');
    const val   = input?.value.trim();
    if (!val) { input?.focus(); return; }
    objectives.push(val); input.value = ''; renderObjectives(); input.focus();
    showError('err-objectives', false);
}
function removeObjective(idx) {
    objectives.splice(idx, 1); renderObjectives();
    showError('err-objectives', objectives.length === 0);
}
function renderObjectives() {
    const list   = document.getElementById('obj-list');
    const hidden = document.getElementById('obj-hidden');
    if (list)   list.innerHTML   = objectives.map((o, i) =>
        `<li><span>${escapeHtml(o)}</span>
             <button type="button" class="obj-remove" onclick="removeObjective(${i})" title="Remove">
                 <i class="fas fa-times"></i>
             </button></li>`
    ).join('');
    if (hidden) hidden.innerHTML = objectives.map(o =>
        `<input type="hidden" name="objectives[]" value="${escapeHtml(o)}">`
    ).join('');
}
document.getElementById('obj-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); addObjective(); }
});

/* ── Inline error clearing ── */
document.querySelectorAll('.form-control').forEach(el => {
    el.addEventListener('input',  () => { markInvalid(el, false); showError('err-' + (el.name || el.id), false); });
    el.addEventListener('change', () => { markInvalid(el, false); showError('err-' + (el.name || el.id), false); });
});

/* ── Bootstrap existing DB data on load ── */
document.addEventListener('DOMContentLoaded', () => {

    /* ── Mode of conduct ── */
    const modeChecked = document.querySelector('[name="mode_of_conduct"]:checked');
    if (modeChecked) handleMode(modeChecked.value);

    /* ── Funds ── */
    const fundsChecked = document.querySelector('[name="funds"]:checked');
    if (fundsChecked) handleFunds(fundsChecked.value);

    /* ── Level tags: old() takes priority, then DB value ── */
    @if(old('level'))
        @foreach(old('level', []) as $lvl)
            tags['level'].push(@json($lvl));
        @endforeach
    @else
        @php
            $dbLevels = is_array($activity->level)
                ? $activity->level
                : (filled($activity->level) ? [$activity->level] : []);
        @endphp
        @foreach($dbLevels as $lvl)
            tags['level'].push(@json($lvl));
        @endforeach
    @endif
    renderTags('level');
    syncLevelPresetButtons();

    /* ── Dept tags ── */
    @if(old('department'))
        @foreach(old('department', []) as $dept)
            tags['dept'].push(@json($dept));
        @endforeach
    @else
        @php
            $dbDepts = is_array($activity->department)
                ? $activity->department
                : (filled($activity->department) ? [$activity->department] : []);
        @endphp
        @foreach($dbDepts as $dept)
            tags['dept'].push(@json($dept));
        @endforeach
    @endif
    renderTags('dept');

    /* ── Objectives ── */
    @if(old('objectives'))
        @foreach(old('objectives', []) as $obj)
            objectives.push(@json($obj));
        @endforeach
    @else
        @php
            $dbObjs = is_array($activity->objectives)
                ? $activity->objectives
                : (filled($activity->objectives) ? [$activity->objectives] : []);
        @endphp
        @foreach($dbObjs as $obj)
            objectives.push(@json($obj));
        @endforeach
    @endif
    renderObjectives();

    /* ── Jump to first failing step on server-side errors ── */
    @if($errors->any())
        for (let s = 1; s <= TOTAL_STEPS; s++) {
            if (!validateStep(s, true)) break;
        }
    @endif

    checkLateSubmission();
});
</script>
@endsection
