<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SarfDocument;
use App\Models\Remark;
use App\Models\SystemLog;
use App\Support\SarfListFilters;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    /**
     * Approval fields in sequential order — each unlocks only after the previous is approved.
     */
    private const MAIN_FIELDS = [
        'approval_dean_sa',
        'approval_avp_sps',
        'approval_dir_basic_ed',
        'approval_vp_acad',
        'approval_vp_hrd_legal',
    ];

    private const FINANCE_FIELDS = [
        'approval_auditing',
        'approval_comptroller_initial',
        'approval_finance_initial',
        'approval_osa_finance',
        'approval_finance_final',
        'approval_comptroller_final',
    ];

    private const ALL_APPROVAL_FIELDS = [
        ...self::MAIN_FIELDS,
        ...self::FINANCE_FIELDS,
    ];

    private const REMARK_MAP = [
        'approval_dean_sa'         => 'remarks_dean_sa',
        'approval_avp_sps'         => 'remarks_avp_sps',
        'approval_dir_basic_ed'    => 'remarks_dir_basic_ed',
        'approval_vp_acad'         => 'remarks_vp_acad',
        'approval_vp_hrd_legal'    => 'remarks_vp_hrd_legal',
        'approval_auditing'              => 'remarks_auditing',
        'approval_comptroller_initial'   => 'remarks_comptroller_initial',
        'approval_finance_initial'       => 'remarks_finance_initial',
        'approval_osa_finance'           => 'remarks_osa_finance',
        'approval_finance_final'         => 'remarks_finance_final',
        'approval_comptroller_final'     => 'remarks_comptroller_final',
    ];

    private const BUDGET_MAP = [
        'approval_dean_sa'         => 'budget_dean_sa',
        'approval_avp_sps'         => 'budget_avp_sps',
        'approval_dir_basic_ed'    => 'budget_dir_basic_ed',
        'approval_vp_acad'         => 'budget_vp_acad',
        'approval_vp_hrd_legal'    => 'budget_vp_hrd_legal',
        'approval_auditing'              => 'budget_auditing',
        'approval_comptroller_initial'   => 'budget_comptroller_initial',
        'approval_finance_initial'       => 'budget_finance_initial',
        'approval_osa_finance'           => 'budget_osa_finance',
        'approval_finance_final'         => 'budget_finance_final',
        'approval_comptroller_final'     => 'budget_comptroller_final',
    ];

    private const APPROVED_AT_MAP = [
        'approval_dean_sa'               => 'approved_at_dean_sa',
        'approval_avp_sps'               => 'approved_at_avp_sps',
        'approval_dir_basic_ed'          => 'approved_at_dir_basic_ed',
        'approval_vp_acad'               => 'approved_at_vp_acad',
        'approval_vp_hrd_legal'          => 'approved_at_vp_hrd_legal',
        'approval_auditing'              => 'approved_at_auditing',
        'approval_comptroller_initial'   => 'approved_at_comptroller_initial',
        'approval_finance_initial'       => 'approved_at_finance_initial',
        'approval_osa_finance'           => 'approved_at_osa_finance',
        'approval_finance_final'         => 'approved_at_finance_final',
        'approval_comptroller_final'     => 'approved_at_comptroller_final',
    ];

    private const RESCHEDULE_APPROVAL_MAP = [
        'approval_dean_sa'               => 'reschedule_approval_dean_sa',
        'approval_avp_sps'               => 'reschedule_approval_avp_sps',
        'approval_dir_basic_ed'          => 'reschedule_approval_dir_basic_ed',
        'approval_vp_acad'               => 'reschedule_approval_vp_acad',
        'approval_vp_hrd_legal'          => 'reschedule_approval_vp_hrd_legal',
        'approval_auditing'              => 'reschedule_approval_auditing',
        'approval_comptroller_initial'   => 'reschedule_approval_comptroller_initial',
        'approval_finance_initial'       => 'reschedule_approval_finance_initial',
        'approval_osa_finance'           => 'reschedule_approval_osa_finance',
        'approval_finance_final'         => 'reschedule_approval_finance_final',
        'approval_comptroller_final'     => 'reschedule_approval_comptroller_final',
    ];

    private const RESCHEDULE_REMARK_MAP = [
        'approval_dean_sa'               => 'reschedule_remarks_dean_sa',
        'approval_avp_sps'               => 'reschedule_remarks_avp_sps',
        'approval_dir_basic_ed'          => 'reschedule_remarks_dir_basic_ed',
        'approval_vp_acad'               => 'reschedule_remarks_vp_acad',
        'approval_vp_hrd_legal'          => 'reschedule_remarks_vp_hrd_legal',
        'approval_auditing'              => 'reschedule_remarks_auditing',
        'approval_comptroller_initial'   => 'reschedule_remarks_comptroller_initial',
        'approval_finance_initial'       => 'reschedule_remarks_finance_initial',
        'approval_osa_finance'           => 'reschedule_remarks_osa_finance',
        'approval_finance_final'         => 'reschedule_remarks_finance_final',
        'approval_comptroller_final'     => 'reschedule_remarks_comptroller_final',
    ];

    private const RESCHEDULE_APPROVED_AT_MAP = [
        'approval_dean_sa'               => 'reschedule_approved_at_dean_sa',
        'approval_avp_sps'               => 'reschedule_approved_at_avp_sps',
        'approval_dir_basic_ed'          => 'reschedule_approved_at_dir_basic_ed',
        'approval_vp_acad'               => 'reschedule_approved_at_vp_acad',
        'approval_vp_hrd_legal'          => 'reschedule_approved_at_vp_hrd_legal',
        'approval_auditing'              => 'reschedule_approved_at_auditing',
        'approval_comptroller_initial'   => 'reschedule_approved_at_comptroller_initial',
        'approval_finance_initial'       => 'reschedule_approved_at_finance_initial',
        'approval_osa_finance'           => 'reschedule_approved_at_osa_finance',
        'approval_finance_final'         => 'reschedule_approved_at_finance_final',
        'approval_comptroller_final'     => 'reschedule_approved_at_comptroller_final',
    ];

    private const RESCHEDULE_APPROVED_BY_MAP = [
        'approval_dean_sa'               => 'reschedule_approved_by_dean_sa',
        'approval_avp_sps'               => 'reschedule_approved_by_avp_sps',
        'approval_dir_basic_ed'          => 'reschedule_approved_by_dir_basic_ed',
        'approval_vp_acad'               => 'reschedule_approved_by_vp_acad',
        'approval_vp_hrd_legal'          => 'reschedule_approved_by_vp_hrd_legal',
        'approval_auditing'              => 'reschedule_approved_by_auditing',
        'approval_comptroller_initial'   => 'reschedule_approved_by_comptroller_initial',
        'approval_finance_initial'       => 'reschedule_approved_by_finance_initial',
        'approval_osa_finance'           => 'reschedule_approved_by_osa_finance',
        'approval_finance_final'         => 'reschedule_approved_by_finance_final',
        'approval_comptroller_final'     => 'reschedule_approved_by_comptroller_final',
    ];

    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $status  = trim((string) $request->query('status', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filters = SarfListFilters::fromRequest($request);
        if ($status !== '') {
            $filters['pipeline_status'] = $status;
        }

        $approvalStatuses = ['pending', 'ongoing', 'for approval', 'for approval finance', 'approved', 'for approval for rescheduling'];

        $query = Activity::with(['branch'])
            ->whereIn('status', $approvalStatuses)
            ->when($search !== '', fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                      ->orWhere('code',  'like', "%{$search}%");
            }));

        SarfListFilters::apply($query, $filters, $approvalStatuses);

        $filteredActivities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);
        $activities = SarfListFilters::paginateCollection($filteredActivities, $request, $perPage);

        // Status counts for the summary chips
        $counts = Activity::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('Dean_OSA.approval.index', [
            'activities' => $activities,
            'counts' => $counts,
            'filters' => $filters,
            ...SarfListFilters::viewData(),
        ]);

        
    }

    public function show(string $id)
    {
        $activity = Activity::with([
            'sarfDocuments',
            'sarfDocuments.remarks',
            'branch',
            'receivedBy',
            'encodedBy',
        ])->findOrFail($id);

        return view('Dean_OSA.approval.show', compact('activity'));
    }

    /**
     * Automatically sets status to 'ongoing' when the action button is clicked
     * (only if currently 'pending'), then redirects to the show/review page.
     */
    public function review(string $id)
    {
        $activity = Activity::findOrFail($id);

        if ($activity->status === 'for approval for rescheduling' && $activity->reschedule_status === 'pending') {
            $activity->update(['reschedule_status' => 'for approval']);

            return redirect()->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => 4]);
        }

        if ($activity->status === 'pending') {
            $activity->update(['status' => 'ongoing']);
        }

        return redirect()->route($this->routeName('approval.show'), $activity->id);
    }

    /**
     * Advance / change the workflow status of an activity.
     */
    public function updateStatus(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,ongoing,for approval,for approval finance,approved,completed,for revision,cancelled,for approval for rescheduling',
            'current_tab' => 'nullable|integer|in:1,2,3,4',
            'focus' => 'nullable|string|max:80|regex:/^[A-Za-z0-9_-]+$/',
        ]);

        $oldStatus = $activity->status;
        $newStatus = $request->input('status');
        $activity->update(['status' => $newStatus]);

        SystemLog::record('Updated status', 'Approval', [
            'subject_type' => Activity::class,
            'subject_id' => $activity->id,
            'subject_label' => $activity->code,
            'description' => "Changed {$activity->code} from {$oldStatus} to {$newStatus}.",
        ]);

        $tab = (int) $request->input('current_tab', 1);
        $focus = $request->input('focus');
        $params = ['id' => $activity->id, 'tab' => $tab];

        if ($focus) {
            $params['focus'] = $focus;
        }

        return redirect()
            ->route($this->routeName('approval.show'), $params)
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Save an individual signatory approval + optional remark.
     * Automatically advances status to 'for approval finance' when all main
     * signatories approve, and to 'approved' when finance signatories approve.
     * If disapproved, sets activity status to 'for revision'.
     */
    public function approve(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        // Block signatory approvals while a reschedule request is active.
        if ($activity->status === 'for approval for rescheduling' || in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature'], true)) {
            return back()->withErrors([
                'approver' => 'Signatory approvals are paused while a reschedule request is active.',
            ]);
        }

        $request->validate([
            'approver' => 'required|in:' . implode(',', self::ALL_APPROVAL_FIELDS),
            'status'   => 'required|in:pending,for signature,approved,disapproved',
            'remark'   => 'nullable|string|max:500',
            'approved_budget' => 'nullable|numeric|min:0',
            'current_tab' => 'nullable|integer|in:1,2,3,4',
        ]);

        $approver = $request->input('approver');
        $approvalStatus = $request->input('status');
        $remark   = $request->input('remark');
        $approvedBudget = $approvalStatus === 'approved' && $request->filled('approved_budget')
            ? $request->input('approved_budget')
            : null;
        $applicableApprovalFields = $this->applicableApprovalFields($activity);

        if (! in_array($approver, $applicableApprovalFields, true)) {
            return back()->withErrors([
                'approver' => 'This approval is not required for this SARF.',
            ]);
        }

        $updates = [
            $approver                      => $approvalStatus,
            self::REMARK_MAP[$approver]    => $remark,
            self::BUDGET_MAP[$approver]    => $approvedBudget,
            self::APPROVED_AT_MAP[$approver] => $approvalStatus === 'approved'
                ? ($activity->{self::APPROVED_AT_MAP[$approver]} ?? now())
                : null,
        ];

        // If disapproved, set activity status to 'for revision'
        if ($approvalStatus === 'disapproved') {
            $updates['status'] = 'for revision';
        }

        $activity->update($updates);
        $activity = Activity::findOrFail($id); // Fresh instance from DB

        SystemLog::record('Updated approval', 'Approval', [
            'subject_type' => Activity::class,
            'subject_id' => $activity->id,
            'subject_label' => $activity->code,
            'description' => "Set {$approver} to {$approvalStatus} for {$activity->code}.",
        ]);

        // Auto-advance: if all main signatories approved → move to finance stage
        $applicableMainFields = $this->applicableMainFields($activity);
        $applicableFinanceFields = $this->applicableFinanceFields($activity);

        $allMainApproved = collect($applicableMainFields)
            ->every(fn($f) => $activity->{$f} === 'approved');

        if ($allMainApproved && $activity->status === 'for approval') {
            $activity->update([
                'status' => count($applicableFinanceFields) > 0 ? 'for approval finance' : 'approved',
            ]);
            SystemLog::record('Auto advanced status', 'Approval', [
                'subject_type' => Activity::class,
                'subject_id' => $activity->id,
                'subject_label' => $activity->code,
                'description' => "{$activity->code} advanced after main approvals were completed.",
            ]);
            $activity = Activity::findOrFail($id); // Fresh instance
        }

        // Auto-advance: if all finance signatories approved → approved (not completed)
        $allFinanceApproved = collect($applicableFinanceFields)
            ->every(fn($f) => $activity->{$f} === 'approved');

        if (count($applicableFinanceFields) > 0 && $allFinanceApproved && $activity->status === 'for approval finance') {
            $activity->update(['status' => 'approved']);
            SystemLog::record('Auto approved activity', 'Approval', [
                'subject_type' => Activity::class,
                'subject_id' => $activity->id,
                'subject_label' => $activity->code,
                'description' => "{$activity->code} became approved after finance approvals were completed.",
            ]);
            $activity = Activity::findOrFail($id); // Fresh instance
        }

        $tab = (int) $request->input('current_tab', 2);
        $focus = $this->approvalFocusTarget($activity, $approver, $approvalStatus);
        if ($focus === 'approved-sarf-section') {
            $tab = 3;
        }

        return redirect()
            ->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => $tab, 'focus' => $focus])
            ->with('success', 'Approval updated successfully.');
    }

    private function approvalFocusTarget(Activity $activity, string $approver, string $approvalStatus): string
    {
        if ($approvalStatus !== 'approved') {
            return 'approval-card-' . $approver;
        }

        foreach ($this->applicableApprovalFields($activity) as $field) {
            if ($activity->{$field} !== 'approved') {
                return 'approval-card-' . $field;
            }
        }

        return 'approved-sarf-section';
    }

    private function applicableApprovalFields(Activity $activity): array
    {
        return [
            ...$this->applicableMainFields($activity),
            ...$this->applicableFinanceFields($activity),
        ];
    }

    private function applicableMainFields(Activity $activity): array
    {
        return array_values(array_filter(
            self::MAIN_FIELDS,
            fn($field) => ($field !== 'approval_dir_basic_ed' || $this->requiresBasicEdApproval($activity))
                && ($field !== 'approval_vp_hrd_legal' || $this->requiresLegalApproval($activity))
        ));
    }

    private function applicableFinanceFields(Activity $activity): array
    {
        return $this->requiresFinanceApproval($activity) ? self::FINANCE_FIELDS : [];
    }

    private function requiresBasicEdApproval(Activity $activity): bool
    {
        $levels = is_array($activity->level)
            ? $activity->level
            : (filled($activity->level) ? [$activity->level] : []);

        return collect($levels)->contains(function ($level) {
            $level = strtolower((string) $level);

            return str_contains($level, 'elementary')
                || str_contains($level, 'junior high')
                || str_contains($level, 'senior high')
                || str_contains($level, 'basic')
                || str_contains($level, 'all levels');
        });
    }

    private function requiresFinanceApproval(Activity $activity): bool
    {
        return $activity->funds === 'With Budget';
    }

    private function requiresLegalApproval(Activity $activity): bool
    {
        return $activity->waiver_consent === 'With';
    }

    private function resetRescheduleApprovals(Activity $activity): array
    {
        $updates = [];

        foreach (self::RESCHEDULE_APPROVAL_MAP as $approvalField => $rescheduleField) {
            $isApplicable = in_array($approvalField, $this->applicableApprovalFields($activity), true);
            $updates[$rescheduleField] = $isApplicable ? 'pending' : null;
            $updates[self::RESCHEDULE_REMARK_MAP[$approvalField]] = null;
            $updates[self::RESCHEDULE_APPROVED_AT_MAP[$approvalField]] = null;
            $updates[self::RESCHEDULE_APPROVED_BY_MAP[$approvalField]] = null;
        }

        return $updates;
    }

    public function storeDocument(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $approvedSarfType = 'APPROVED_SARF';

        if (! in_array($activity->status, ['approved', 'completed'], true)) {
            return back()->withErrors([
                'approved_sarf_file' => 'You can only upload the approved SARF once the activity is approved.',
            ]);
        }

        $existingDocument = SarfDocument::where('activity_id', $activity->id)
            ->where('type', $approvedSarfType)
            ->first();

        $request->validate([
            'current_tab' => 'nullable|integer|in:1,2,3,4',
            'approved_remark' => 'nullable|string|max:1000',
            'approved_sarf_hardcopy' => 'nullable|boolean',
            'approved_sarf_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ]);

        $remark = trim((string) $request->input('approved_remark', ''));
        $hardcopyAvailable = $request->boolean('approved_sarf_hardcopy');

        if (! $existingDocument && ! $request->hasFile('approved_sarf_file') && ! $hardcopyAvailable) {
            return back()
                ->withErrors(['approved_sarf_hardcopy' => 'Upload a PDF or check that the approved SARF hardcopy is available.'])
                ->withInput();
        }

        if ($request->hasFile('approved_sarf_file')) {
            $file = $request->file('approved_sarf_file');
            $path = $file->store('sarf_documents', 'public');

            if ($existingDocument?->file_path) {
                Storage::disk('public')->delete($existingDocument->file_path);
            }

            $document = SarfDocument::updateOrCreate(
                ['activity_id' => $activity->id, 'type' => $approvedSarfType],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
            );
        } else {
            $document = $existingDocument;

            if (! $document && $hardcopyAvailable) {
                $document = SarfDocument::create([
                    'activity_id' => $activity->id,
                    'type' => $approvedSarfType,
                    'file_path' => null,
                    'original_filename' => null,
                ]);
            }
        }

        if ($document && $remark !== '') {
            Remark::updateOrCreate(
                ['activity_id' => $activity->id, 'sarf_document_id' => $document->id],
                ['remark' => $remark]
            );
        }

        SystemLog::record('Uploaded approved SARF', 'Documents', [
            'subject_type' => Activity::class,
            'subject_id' => $activity->id,
            'subject_label' => $activity->code,
            'description' => $document->file_path
                ? "Uploaded approved SARF for {$activity->code}."
                : "Marked approved SARF hardcopy available for {$activity->code}.",
        ]);

        $tab = (int) $request->input('current_tab', 3);
        return redirect()
            ->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => $tab])
            ->with('success', $document->file_path ? 'Approved SARF uploaded successfully.' : 'Approved SARF hardcopy saved successfully.');
    }

    /* ══════════════════════════════════════════════
       RESCHEDULING — separate from signatory approvals
       ══════════════════════════════════════════════ */

    /**
     * Submit a reschedule request.
     * Sets reschedule_status = 'pending' which freezes the signatory pipeline.
     */
    public function requestReschedule(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'reschedule_date'   => 'required|date|after_or_equal:today',
            'reschedule_time'   => 'nullable|string|max:100',
            'reschedule_venue'  => 'nullable|string|max:255',
            'reschedule_reason' => 'required|string|max:1000',
        ]);

        $reschedulePaper = SarfDocument::where('activity_id', $activity->id)
            ->where('type', 'RESCHEDULE_PAPER')
            ->first();

        if ($reschedulePaper?->file_path) {
            Storage::disk('public')->delete($reschedulePaper->file_path);
        }

        if ($reschedulePaper) {
            $reschedulePaper->delete();
        }

        $activity->update([
            'reschedule_status'       => 'pending',
            'reschedule_original_date' => $activity->date_of_activity,
            'reschedule_original_time' => $activity->time_of_activity,
            'reschedule_original_mode' => $activity->mode_of_conduct,
            'reschedule_original_venue' => $activity->venue,
            'reschedule_original_venue_type' => $activity->venue_type,
            'reschedule_original_platform' => $activity->platform,
            'reschedule_date'         => $request->input('reschedule_date'),
            'reschedule_time'         => $request->input('reschedule_time'),
            'reschedule_mode'         => $activity->mode_of_conduct,
            'reschedule_venue'        => $request->input('reschedule_venue'),
            'reschedule_venue_type'   => $activity->venue_type,
            'reschedule_platform'     => $activity->mode_of_conduct === 'Online' ? $request->input('reschedule_venue') : $activity->platform,
            'reschedule_reason'       => $request->input('reschedule_reason'),
            'reschedule_remarks'      => null,
            'reschedule_requested_at' => now(),
            'reschedule_decided_at'   => null,
            'status'                  => 'for approval for rescheduling',
            ...$this->resetRescheduleApprovals($activity),
        ]);

        SystemLog::record('Requested reschedule', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Reschedule requested for {$activity->code} to {$request->input('reschedule_date')}.",
        ]);

        return redirect()
            ->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => 4])
            ->with('success', 'Reschedule request submitted. Signatory approvals are paused until this is resolved.');
    }

    /**
     * Approve the reschedule — update the activity's date/time/venue
     * and clear the reschedule fields. Pipeline resumes.
     * All existing signatory approvals remain intact.
     */
    public function approveReschedule(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $existingReschedulePaper = SarfDocument::where('activity_id', $activity->id)
            ->where('type', 'RESCHEDULE_PAPER')
            ->first();
        $saveAction = $request->input('save_action', 'approval');

        // Document upload — allowed when reschedule_status is already 'approved'
        if ($saveAction === 'document') {
            if ($activity->reschedule_status !== 'approved') {
                return back()->withErrors(['reschedule' => 'Save the reschedule approval as Approved first before uploading documents.']);
            }

            $request->validate([
                'reschedule_paper_hardcopy' => 'nullable|boolean',
                'reschedule_paper_file' => [
                    'nullable',
                    'file',
                    'mimes:pdf',
                    'max:10240',
                ],
            ]);

            $hardcopyAvailable = $request->boolean('reschedule_paper_hardcopy');

            if (! $existingReschedulePaper && ! $request->hasFile('reschedule_paper_file') && ! $hardcopyAvailable) {
                return back()
                    ->withErrors(['reschedule_paper_hardcopy' => 'Upload a PDF or check that the reschedule paper hardcopy is available.'])
                    ->withInput();
            }

            if ($request->hasFile('reschedule_paper_file')) {
                $file = $request->file('reschedule_paper_file');
                $path = $file->store('sarf_documents', 'public');

                if ($existingReschedulePaper?->file_path) {
                    Storage::disk('public')->delete($existingReschedulePaper->file_path);
                }

                SarfDocument::updateOrCreate(
                    ['activity_id' => $activity->id, 'type' => 'RESCHEDULE_PAPER'],
                    ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
                );
            } elseif (! $existingReschedulePaper && $hardcopyAvailable) {
                SarfDocument::create([
                    'activity_id' => $activity->id,
                    'type' => 'RESCHEDULE_PAPER',
                    'file_path' => null,
                    'original_filename' => null,
                ]);
            }

            $activity->update(['status' => 'approved']);

            SystemLog::record('Updated reschedule document', 'Reschedule', [
                'subject_type'  => Activity::class,
                'subject_id'    => $activity->id,
                'subject_label' => $activity->code,
                'description'   => "Updated reschedule document for {$activity->code}.",
            ]);

            return redirect()
                ->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => 4])
                ->with('success', 'Reschedule document saved successfully.');
        }

        // Approval status update — requires a pending/for approval/for signature reschedule
        if ($activity->status !== 'for approval for rescheduling' || ! in_array($activity->reschedule_status, ['pending', 'for approval', 'for signature', 'approved'], true)) {
            return back()->withErrors(['reschedule' => 'No reschedule request is ready for approval.']);
        }

        $request->validate([
            'reschedule_approver' => 'required|in:' . implode(',', array_keys(self::RESCHEDULE_APPROVAL_MAP)),
            'reschedule_status' => 'required|in:pending,for signature,approved,disapproved',
            'reschedule_remarks' => 'nullable|string|max:1000',
            'approved_budget' => 'nullable|numeric|min:0',
        ]);

        $approver = $request->input('reschedule_approver');
        $rescheduleStatus = $request->input('reschedule_status');
        $applicableApprovalFields = $this->applicableApprovalFields($activity);

        if (! in_array($approver, $applicableApprovalFields, true)) {
            return back()->withErrors([
                'reschedule_approver' => 'This approval is not required for this SARF.',
            ]);
        }

        $statusField = self::RESCHEDULE_APPROVAL_MAP[$approver];
        $remarkField = self::RESCHEDULE_REMARK_MAP[$approver];
        $approvedAtField = self::RESCHEDULE_APPROVED_AT_MAP[$approver];
        $approvedByField = self::RESCHEDULE_APPROVED_BY_MAP[$approver];

        $approvedBudget = $rescheduleStatus === 'approved' && $request->filled('approved_budget')
            ? $request->input('approved_budget')
            : null;

        $updates = [
            $statusField => $rescheduleStatus,
            $remarkField => $request->input('reschedule_remarks'),
            $approvedAtField => $rescheduleStatus === 'approved'
                ? ($activity->{$approvedAtField} ?? now())
                : null,
            $approvedByField => $rescheduleStatus === 'approved'
                ? ($activity->{$approvedByField} ?? Auth::id())
                : null,
        ];

        $budgetField = self::BUDGET_MAP[$approver] ?? null;
        if ($budgetField) {
            $updates[$budgetField] = $approvedBudget;
        }

        if ($rescheduleStatus === 'disapproved') {
            $updates['reschedule_status'] = 'disapproved';
            $updates['reschedule_decided_at'] = now();
            $updates['status'] = 'for reschedule';
            $updates['modification_type'] = 'rescheduling';
            $updates['modification_remarks'] = 'Schedule was disapproved. Please revise the schedule and resubmit.';
            $updates['reschedule_date'] = null;
            $updates['reschedule_time'] = null;
            $updates['reschedule_mode'] = null;
            $updates['reschedule_venue'] = null;
            $updates['reschedule_venue_type'] = null;
            $updates['reschedule_platform'] = null;
        }

        $activity->update($updates);
        $activity = Activity::findOrFail($id);

        if ($rescheduleStatus === 'disapproved') {
            $disapprovalRemark = $request->input('reschedule_remarks') ?: 'No remarks provided.';
            $reschedulePaper = SarfDocument::where('activity_id', $activity->id)
                ->where('type', 'RESCHEDULE_PAPER')
                ->first();

            if ($reschedulePaper?->file_path) {
                Storage::disk('public')->delete($reschedulePaper->file_path);
            }

            if ($reschedulePaper) {
                $reschedulePaper->delete();
            }

            $activity->update([
                'modification_remarks' => "Schedule was disapproved: {$disapprovalRemark} Please revise the schedule and resubmit.",
            ]);
        }

        if ($rescheduleStatus !== 'disapproved') {
            $allRescheduleApproved = collect($applicableApprovalFields)
                ->every(fn($field) => $activity->{self::RESCHEDULE_APPROVAL_MAP[$field]} === 'approved');

            if ($allRescheduleApproved) {
                $activity->update([
                    'reschedule_status' => 'approved',
                    'reschedule_decided_at' => now(),
                    'date_of_activity' => $activity->reschedule_date,
                    'time_of_activity' => $activity->reschedule_time,
                    'mode_of_conduct' => $activity->reschedule_mode ?: $activity->mode_of_conduct,
                    'venue' => $activity->reschedule_venue,
                    'venue_type' => $activity->reschedule_venue_type,
                    'platform' => $activity->reschedule_platform,
                ]);
                $activity = Activity::findOrFail($id);
            } else {
                $activity->update(['reschedule_status' => 'for approval']);
            }
        }

        SystemLog::record('Updated reschedule approval', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Set {$approver} reschedule approval for {$activity->code} to {$rescheduleStatus}.",
        ]);

        if ($rescheduleStatus === 'disapproved') {
            return redirect()
                ->route($this->routeName('approval.index'))
                ->with('success', 'Reschedule disapproved. Activity sent back for schedule re-editing.');
        }

        $focus = $this->rescheduleApprovalFocusTarget($activity, $approver, $rescheduleStatus);

        return redirect()
            ->route($this->routeName('approval.show'), ['id' => $activity->id, 'tab' => 4, 'focus' => $focus])
            ->with('success', 'Reschedule approval updated successfully.');
    }

    private function rescheduleApprovalFocusTarget(Activity $activity, string $approver, string $approvalStatus): string
    {
        if ($approvalStatus !== 'approved') {
            return 'reschedule-approval-card-' . $approver;
        }

        foreach ($this->applicableApprovalFields($activity) as $field) {
            if (($activity->{self::RESCHEDULE_APPROVAL_MAP[$field]} ?? 'pending') !== 'approved') {
                return 'reschedule-approval-card-' . $field;
            }
        }

        return 'reschedule-document-section';
    }

    /**
     * Reject the reschedule — activity goes back to 'for reschedule' status
     * so the user can re-edit the schedule. The rescheduling process must
     * be completed (approved) before the activity returns to the approval pipeline.
     */
    public function rejectReschedule(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        if ($activity->status !== 'for approval for rescheduling' || ! in_array($activity->reschedule_status, ['pending', 'for approval'], true)) {
            return back()->withErrors(['reschedule' => 'No pending reschedule request.']);
        }

        $request->validate([
            'reschedule_remarks' => 'nullable|string|max:1000',
        ]);

        $activity->update([
            'reschedule_status'     => 'rejected',
            'reschedule_remarks'    => $request->input('reschedule_remarks'),
            'reschedule_decided_at' => now(),
            'reschedule_date'       => null,
            'reschedule_time'       => null,
            'reschedule_mode'       => null,
            'reschedule_venue'      => null,
            'reschedule_venue_type' => null,
            'reschedule_platform'   => null,
            // Send back to Activities module for re-editing
            'status'                => 'for reschedule',
            'modification_type'     => 'rescheduling',
            'modification_remarks'  => 'Schedule was rejected: ' . ($request->input('reschedule_remarks') ?: 'No remarks provided.') . ' Please revise the schedule and resubmit.',
        ]);

        $reschedulePaper = SarfDocument::where('activity_id', $activity->id)
            ->where('type', 'RESCHEDULE_PAPER')
            ->first();

        if ($reschedulePaper?->file_path) {
            Storage::disk('public')->delete($reschedulePaper->file_path);
        }

        if ($reschedulePaper) {
            $reschedulePaper->delete();
        }

        SystemLog::record('Rejected reschedule', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Reschedule rejected for {$activity->code}. Sent back for re-editing.",
        ]);

        return redirect()
            ->route($this->routeName('approval.index'))
            ->with('success', 'Reschedule rejected. Activity sent back for schedule re-editing.');
    }

    /* ══════════════════════════════════════════════
       MODIFICATION — send activity back for revision or rescheduling
       ══════════════════════════════════════════════ */

    /**
     * Send an activity back to the Activities module for modification.
     * Sets modification_type = revision | rescheduling so the edit page
     * knows what kind of changes to expect.
     *
     * Revision  → status = 'for revision', follows normal revision flow
     * Rescheduling → status = 'for reschedule', requires schedule approval after edit
     */
    public function requestModification(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'modification_type'    => 'required|in:revision,rescheduling',
            'modification_remarks' => 'nullable|string|max:1000',
        ]);

        $type    = $request->input('modification_type');
        $remarks = $request->input('modification_remarks');

        $canRequestRevision = in_array($activity->status, ['pending', 'ongoing', 'for approval', 'for approval finance'], true);
        $canRequestRescheduling = $activity->status === 'approved';

        if ($type === 'revision' && ! $canRequestRevision) {
            return back()->withErrors([
                'modification_type' => 'Revision requests are only available for pending, ongoing, or approval-stage activities.',
            ]);
        }

        if ($type === 'rescheduling' && ! $canRequestRescheduling) {
            return back()->withErrors([
                'modification_type' => 'Rescheduling requests are only available after the activity is approved.',
            ]);
        }

        $newStatus = $type === 'rescheduling' ? 'for reschedule' : 'for revision';

        if ($type === 'rescheduling') {
            $reschedulePaper = SarfDocument::where('activity_id', $activity->id)
                ->where('type', 'RESCHEDULE_PAPER')
                ->first();

            if ($reschedulePaper?->file_path) {
                Storage::disk('public')->delete($reschedulePaper->file_path);
            }

            if ($reschedulePaper) {
                $reschedulePaper->delete();
            }
        }

        $updateData = [
            'modification_type'    => $type,
            'modification_remarks' => $remarks,
            'status'               => $newStatus,
            'reschedule_status'    => $type === 'rescheduling' ? null : $activity->reschedule_status,
        ];

        if ($type === 'rescheduling') {
            $updateData['reschedule_reason'] = filled($remarks) ? $remarks : null;
        }

        $activity->update($updateData);

        $actionLabel = $type === 'rescheduling'
            ? 'Requested Rescheduling Modification'
            : 'Requested Revision Modification';

        SystemLog::record($actionLabel, 'Modification', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "{$activity->code} was sent back for " . ($type === 'rescheduling' ? 'schedule changes' : 'content revision') . ". " . ($remarks ? "Remarks: {$remarks}" : 'No remarks provided.'),
        ]);

        return redirect()
            ->route($this->routeName('approval.index'))
            ->with('success', "Activity {$activity->code} sent for " . ucfirst($type) . '. It will now appear in the Activities module for editing.');
    }

    public function destroy(string $id)
    {
        if (auth()->user()?->usertype === 'Staff_OSA') {
            return redirect()
                ->route($this->routeName('approval.index'))
                ->withErrors(['delete' => 'Staff accounts are not allowed to delete approval records.']);
        }

        $activity = Activity::with('sarfDocuments')->findOrFail($id);
        $code = $activity->code;
        $title = $activity->title;

        foreach ($activity->sarfDocuments as $document) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();
        }

        SystemLog::record('Deleted Activity from Approval', 'Approval', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $code,
            'description'   => "{$code} was permanently deleted from the approval module, including uploaded SARF documents. Title: {$title}.",
        ]);

        $activity->delete();

        return redirect()
            ->route($this->routeName('approval.index'))
            ->with('success', "Activity {$code} deleted successfully.");
    }
}
