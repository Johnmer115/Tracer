<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SarfDocument;

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
        'approval_vp_comptroller',
        'approval_avp_finance',
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
        'approval_vp_comptroller'  => 'remarks_vp_comptroller',
        'approval_avp_finance'     => 'remarks_avp_finance',
    ];

    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $status  = trim((string) $request->query('status', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with(['branch'])
            ->when($search !== '', fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                      ->orWhere('code',  'like', "%{$search}%");
            }))
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Status counts for the summary chips
        $counts = Activity::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('Dean_OSA.approval.index', compact('activities', 'counts'));
    }

    public function show(string $id)
    {
        $activity = Activity::with([
            'sarfDocuments',
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
            'status' => 'required|in:pending,ongoing,for approval,for approval finance,completed,for revision,cancelled',
        ]);

        $activity->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Save an individual signatory approval + optional remark.
     * Automatically advances status to 'for approval finance' when all main
     * signatories approve, and to 'completed' when finance signatories approve.
     */
    public function approve(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'approver' => 'required|in:' . implode(',', self::ALL_APPROVAL_FIELDS),
            'status'   => 'required|in:pending,for signature,approved,disapproved',
            'remark'   => 'nullable|string|max:500',
            'current_tab' => 'nullable|integer|in:1,2,3',
        ]);

        $approver = $request->input('approver');
        $status   = $request->input('status');
        $remark   = $request->input('remark');

        $updates = [
            $approver                      => $status,
            self::REMARK_MAP[$approver]    => $remark,
        ];

        $activity->update($updates);
        $activity->refresh();

        // Auto-advance: if all main signatories approved → move to finance stage
        $allMainApproved = collect(self::MAIN_FIELDS)
            ->every(fn($f) => $activity->{$f} === 'approved');

        if ($allMainApproved && $activity->status === 'for approval') {
            $activity->update(['status' => 'for approval finance']);
        }

        // Auto-advance: if all finance signatories approved → completed
        $allFinanceApproved = collect(self::FINANCE_FIELDS)
            ->every(fn($f) => $activity->{$f} === 'approved');

        if ($allFinanceApproved && $activity->status === 'for approval finance') {
            $activity->update(['status' => 'completed']);
        }

        $tab = (int) $request->input('current_tab', 2);
        return redirect()
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => $tab])
            ->with('success', 'Approval updated successfully.');
    }

    public function storeDocument(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        if ($activity->status !== 'completed') {
            return back()->withErrors([
                'approved_doc' => 'You can only upload approved SARF documents once the activity is completed.',
            ]);
        }

        $request->validate([
            'types' => 'required|array|min:1',
            'types.*' => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
            'current_tab' => 'nullable|integer|in:1,2,3',
        ]);

        foreach ($request->input('types', []) as $type) {
            $fileKey = 'file_' . $type;

            if (! $request->hasFile($fileKey)) {
                return back()
                    ->withErrors([$fileKey => "Please upload a PDF for {$type}."])
                    ->withInput();
            }

            $request->validate([
                $fileKey => 'file|mimes:pdf|max:10240',
            ]);

            $file = $request->file($fileKey);
            $path = $file->store('sarf_documents', 'public');

            SarfDocument::updateOrCreate(
                ['activity_id' => $activity->id, 'type' => $type],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
            );
        }

        $tab = (int) $request->input('current_tab', 3);
        return redirect()
            ->route('dean_osa.approval.show', ['id' => $activity->id, 'tab' => $tab])
            ->with('success', 'Approved SARF document(s) uploaded successfully.');
    }
}
