<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SarfDocument;
use App\Models\Remark;
use App\Models\SystemLog;
use App\Support\SarfListFilters;

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

        $query = Activity::with(['branch'])
            ->whereIn('status', ['pending', 'ongoing', 'for approval', 'for approval finance', 'approved'])
            ->when($search !== '', fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                      ->orWhere('code',  'like', "%{$search}%");
            }));

        SarfListFilters::apply($query, $filters, ['pending', 'ongoing', 'for approval', 'for approval finance', 'approved']);

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

        if ($activity->status === 'pending') {
            $activity->update(['status' => 'ongoing']);
        }

        return redirect()->route('dean_osa.approval.show', $activity->id);
    }

    /**
     * Advance / change the workflow status of an activity.
     */
    public function updateStatus(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,ongoing,for approval,for approval finance,approved,completed,for revision,cancelled',
            'current_tab' => 'nullable|integer|in:1,2,3',
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
            ->route('dean_osa.approval.show', $params)
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

        // Block approvals while a reschedule is pending
        if ($activity->reschedule_status === 'pending') {
            return back()->withErrors([
                'approver' => 'Signatory approvals are paused while a reschedule request is pending.',
            ]);
        }

        $request->validate([
            'approver' => 'required|in:' . implode(',', self::ALL_APPROVAL_FIELDS),
            'status'   => 'required|in:pending,for signature,approved,disapproved',
            'remark'   => 'nullable|string|max:500',
            'approved_budget' => 'nullable|numeric|min:0',
            'current_tab' => 'nullable|integer|in:1,2,3',
        ]);

        $approver = $request->input('approver');
        $approvalStatus = $request->input('status');
        $remark   = $request->input('remark');
        $approvedBudget = $request->filled('approved_budget')
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
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => $tab, 'focus' => $focus])
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
            fn($field) => $field !== 'approval_dir_basic_ed' || $this->requiresBasicEdApproval($activity)
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
            'current_tab' => 'nullable|integer|in:1,2,3',
            'approved_remark' => 'nullable|string|max:1000',
            'approved_sarf_file' => [
                $existingDocument ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ]);

        $remark = trim((string) $request->input('approved_remark', ''));

        if ($request->hasFile('approved_sarf_file')) {
            $file = $request->file('approved_sarf_file');
            $path = $file->store('sarf_documents', 'public');

            $document = SarfDocument::updateOrCreate(
                ['activity_id' => $activity->id, 'type' => $approvedSarfType],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
            );
        } else {
            $document = $existingDocument;
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
            'description' => "Uploaded approved SARF for {$activity->code}.",
        ]);

        $tab = (int) $request->input('current_tab', 3);
        return redirect()
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => $tab])
            ->with('success', 'Approved SARF uploaded successfully.');
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

        $activity->update([
            'reschedule_status'       => 'pending',
            'reschedule_date'         => $request->input('reschedule_date'),
            'reschedule_time'         => $request->input('reschedule_time'),
            'reschedule_venue'        => $request->input('reschedule_venue'),
            'reschedule_reason'       => $request->input('reschedule_reason'),
            'reschedule_remarks'      => null,
            'reschedule_requested_at' => now(),
            'reschedule_decided_at'   => null,
        ]);

        SystemLog::record('Requested reschedule', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Reschedule requested for {$activity->code} to {$request->input('reschedule_date')}.",
        ]);

        return redirect()
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => 1])
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

        if ($activity->reschedule_status !== 'pending') {
            return back()->withErrors(['reschedule' => 'No pending reschedule request.']);
        }

        $request->validate([
            'reschedule_remarks' => 'nullable|string|max:1000',
        ]);

        // Apply the new schedule to the activity
        $updates = [
            'date_of_activity'      => $activity->reschedule_date,
            'reschedule_status'     => 'approved',
            'reschedule_remarks'    => $request->input('reschedule_remarks'),
            'reschedule_decided_at' => now(),
        ];

        if (filled($activity->reschedule_time)) {
            $updates['time_of_activity'] = $activity->reschedule_time;
        }

        if (filled($activity->reschedule_venue)) {
            if ($activity->mode_of_conduct === 'Online') {
                $updates['platform'] = $activity->reschedule_venue;
            } else {
                $updates['venue'] = $activity->reschedule_venue;
            }
        }

        $activity->update($updates);

        SystemLog::record('Approved reschedule', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Reschedule approved for {$activity->code}. New date: {$activity->reschedule_date->format('M j, Y')}.",
        ]);

        return redirect()
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => 1])
            ->with('success', 'Reschedule approved. Activity date/time updated. Signatory approvals resume.');
    }

    /**
     * Reject the reschedule — activity goes back to 'for reschedule' status
     * so the user can re-edit the schedule. The rescheduling process must
     * be completed (approved) before the activity returns to the approval pipeline.
     */
    public function rejectReschedule(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        if ($activity->reschedule_status !== 'pending') {
            return back()->withErrors(['reschedule' => 'No pending reschedule request.']);
        }

        $request->validate([
            'reschedule_remarks' => 'nullable|string|max:1000',
        ]);

        $activity->update([
            'reschedule_status'     => 'rejected',
            'reschedule_remarks'    => $request->input('reschedule_remarks'),
            'reschedule_decided_at' => now(),
            // Send back to Activities module for re-editing
            'status'                => 'for reschedule',
            'modification_type'     => 'rescheduling',
            'modification_remarks'  => 'Schedule was rejected: ' . ($request->input('reschedule_remarks') ?: 'No remarks provided.') . ' Please revise the schedule and resubmit.',
        ]);

        SystemLog::record('Rejected reschedule', 'Reschedule', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Reschedule rejected for {$activity->code}. Sent back for re-editing.",
        ]);

        return redirect()
            ->route('dean_osa.approval.index')
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

        $newStatus = $type === 'rescheduling' ? 'for reschedule' : 'for revision';

        $activity->update([
            'modification_type'    => $type,
            'modification_remarks' => $remarks,
            'status'               => $newStatus,
        ]);

        SystemLog::record('Requested modification', 'Modification', [
            'subject_type'  => Activity::class,
            'subject_id'    => $activity->id,
            'subject_label' => $activity->code,
            'description'   => "Sent {$activity->code} for {$type}. " . ($remarks ? "Remarks: {$remarks}" : 'No remarks.'),
        ]);

        return redirect()
            ->route('dean_osa.approval.index')
            ->with('success', "Activity {$activity->code} sent for " . ucfirst($type) . '. It will now appear in the Activities module for editing.');
    }
}
