<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\SarfDocument;
use App\Models\SchoolYear;
use App\Support\SarfListFilters;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filters = SarfListFilters::fromRequest($request);
        $query = Activity::with(['sarfDocuments', 'branch'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title',  'like', "%{$search}%")
                          ->orWhere('code',   'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%");
                });
            });

        SarfListFilters::apply($query, $filters);

        $filteredActivities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);
        $activities = SarfListFilters::paginateCollection($filteredActivities, $request, $perPage);

        return view('Dean_OSA.activity.index', [
            'activities' => $activities,
            'filters' => $filters,
            ...SarfListFilters::viewData(),
        ]);
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $activeSchoolYear = SchoolYear::current();
        $nextSequence = $activeSchoolYear
            ? $this->nextSequenceForSchoolYear($activeSchoolYear->code)
            : null;

        return view('Dean_OSA.activity.create', compact('branches', 'activeSchoolYear', 'nextSequence'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'branch_id'          => 'nullable|exists:branches,id',
            'type_of_activity'   => 'required|in:Extra-Curricular,Co-Curricular',
            'event_type'         => 'required|in:Internal,External',
            'activity_level'     => 'required|in:Organization,Local,Interbranch,Off-Campus',
            'mode_of_conduct'    => 'required|in:Face to Face,Online,Hybrid',
            'date_of_activity'   => 'required|date',
            'funds'              => 'required|in:With Budget,ATC,No Fee',
            'amount'             => 'required_if:funds,With Budget,ATC|nullable|numeric|min:0',
            'types'              => 'nullable|array',
            'types.*'            => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
            'participants_count' => 'nullable|integer|min:0',
        ]);

        $activeSchoolYear = SchoolYear::current();

        if (! $activeSchoolYear) {
            return back()
                ->withErrors(['school_year' => 'Please set a current school year before creating an activity.'])
                ->withInput();
        }

        $modeOfConduct = $request->input('mode_of_conduct');
        $hasVenue      = in_array($modeOfConduct, ['Face to Face', 'Hybrid'], true);
        $hasPlatform   = in_array($modeOfConduct, ['Online', 'Hybrid'], true);
        $funds         = $request->input('funds');

        $activity = DB::transaction(function () use ($request, $activeSchoolYear, $modeOfConduct, $hasVenue, $hasPlatform, $funds) {
            $lockedSchoolYear = SchoolYear::whereKey($activeSchoolYear->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence     = $this->nextSequenceForSchoolYear($lockedSchoolYear->code, true);
            $activityCode = $lockedSchoolYear->code . 's' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            return Activity::create([
                'code'                   => $activityCode,
                'school_year_code'       => $lockedSchoolYear->code,
                'branch_id'              => $request->input('branch_id'),
                'level'                  => $request->input('level'),
                'department'             => $request->input('department', []),
                'title'                  => $request->input('title'),
                'description'            => $request->input('description'),
                'objectives'             => $request->input('objectives', []),
                'type_of_activity'       => $request->input('type_of_activity'),
                'event_type'             => $request->input('event_type'),
                'activity_level'         => $request->input('activity_level'),
                'participants_profile'   => $request->input('participants_profile'),
                'participants_count'     => $request->input('participants_count'),
                'date_of_activity'       => $request->input('date_of_activity'),
                'time_of_activity'       => $request->input('time_of_activity'),
                'public_poster'          => $request->input('public_poster'),
                'mode_of_conduct'        => $modeOfConduct,
                'venue'                  => $hasVenue    ? $request->input('venue')      : null,
                'venue_type'             => $hasVenue    ? $request->input('venue_type') : null,
                'platform'               => $hasPlatform ? $request->input('platform')   : null,
                'funds'                  => $funds,
                'source'                 => $funds === 'With Budget'                       ? $request->input('source')              : null,
                'amount'                 => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('amount')              : null,
                'expected_collection'    => $funds === 'ATC'                               ? $request->input('expected_collection') : null,
                'canteen'                => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('canteen')             : null,
                'procurement'            => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('procurement')         : null,
                'late_submission_reason' => $request->input('late_submission_reason'),
                'received_by'            => Auth::id(),
                'encoded_by'             => Auth::id(),
                'status'                 => 'pending',
            ]);
        });

        foreach ($request->input('types', []) as $type) {
            $fileKey = 'file_' . $type;
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store('sarf_documents', 'public');
                SarfDocument::create([
                    'activity_id'       => $activity->id,
                    'type'              => $type,
                    'file_path'         => $path,
                    'original_filename' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('dean_osa.activity.index')
                         ->with('success', 'Activity created successfully.');
    }

    /**
     * FIX 1: Returns view.blade.php (not show.blade.php)
     * FIX 2: Eager-loads branch, receivedBy, encodedBy for the view
     * FIX 3: Auto-redirect to approval view if activity is approved or completed
     */
    public function show(string $id)
    {
        $activity = Activity::with([
            'sarfDocuments',
            'branch',
            'receivedBy',
            'encodedBy',
        ])->findOrFail($id);

        // Approved/completed activities should keep their approval history visible.
        if (in_array($activity->status, ['approved', 'completed'], true)) {
            return redirect()->route('dean_osa.approval.show', $id);
        }

        return view('Dean_OSA.activity.view', compact('activity'));
    }

    public function edit(string $id)
    {
        $activity = Activity::with('sarfDocuments')->findOrFail($id);
        $branches = Branch::orderBy('name')->get();

        return view('Dean_OSA.activity.edit', compact('activity', 'branches'));
    }

    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'title'              => 'required|string|max:255',
            'branch_id'          => 'nullable|exists:branches,id',
            'type_of_activity'   => 'required|in:Extra-Curricular,Co-Curricular',
            'event_type'         => 'required|in:Internal,External',
            'activity_level'     => 'required|in:Organization,Local,Interbranch,Off-Campus',
            'mode_of_conduct'    => 'required|in:Face to Face,Online,Hybrid',
            'date_of_activity'   => 'required|date',
            'funds'              => 'required|in:With Budget,ATC,No Fee',
            'amount'             => 'required_if:funds,With Budget,ATC|nullable|numeric|min:0',
            'types'              => 'nullable|array',
            'types.*'            => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
            'participants_count' => 'nullable|integer|min:0',
        ]);

        $modeOfConduct = $request->input('mode_of_conduct');
        $hasVenue      = in_array($modeOfConduct, ['Face to Face', 'Hybrid'], true);
        $hasPlatform   = in_array($modeOfConduct, ['Online', 'Hybrid'], true);
        $funds         = $request->input('funds');

        // When updating from 'for revision', reset disapproved approvals to 'pending'
        $resetData = [];
        if ($activity->status === 'for revision') {
            foreach ([
                'approval_dean_sa', 'approval_avp_sps', 'approval_dir_basic_ed',
                'approval_vp_acad', 'approval_vp_hrd_legal',
                'approval_auditing', 'approval_comptroller_initial', 'approval_finance_initial',
                'approval_osa_finance', 'approval_finance_final', 'approval_comptroller_final',
            ] as $field) {
                if ($activity->{$field} === 'disapproved') {
                    $resetData[$field] = 'pending';
                }
            }
            // Return to 'for approval' stage so approvals can continue without re-advancing
            $resetData['status'] = 'for approval';
        }

        $updateData = array_merge([
            'branch_id'              => $request->input('branch_id'),
            'level'                  => $request->input('level'),
            'department'             => $request->input('department', []),
            'title'                  => $request->input('title'),
            'description'            => $request->input('description'),
            'objectives'             => $request->input('objectives', []),
            'type_of_activity'       => $request->input('type_of_activity'),
            'event_type'             => $request->input('event_type'),
            'activity_level'         => $request->input('activity_level'),
            'participants_profile'   => $request->input('participants_profile'),
            'participants_count'     => $request->input('participants_count'),
            'date_of_activity'       => $request->input('date_of_activity'),
            'time_of_activity'       => $request->input('time_of_activity'),
            'public_poster'          => $request->input('public_poster'),
            'mode_of_conduct'        => $modeOfConduct,
            'venue'                  => $hasVenue    ? $request->input('venue')      : null,
            'venue_type'             => $hasVenue    ? $request->input('venue_type') : null,
            'platform'               => $hasPlatform ? $request->input('platform')   : null,
            'funds'                  => $funds,
            'source'                 => $funds === 'With Budget'                       ? $request->input('source')              : null,
            'amount'                 => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('amount')              : null,
            'expected_collection'    => $funds === 'ATC'                               ? $request->input('expected_collection') : null,
            'canteen'                => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('canteen')             : null,
            'procurement'            => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('procurement')         : null,
            'late_submission_reason' => $request->input('late_submission_reason'),
            // received_by / encoded_by intentionally not updated — keep original recorder
        ], $resetData);

        $activity->update($updateData);

        foreach ($request->input('types', []) as $type) {
            $fileKey = 'file_' . $type;
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store('sarf_documents', 'public');
                SarfDocument::updateOrCreate(
                    ['activity_id' => $activity->id, 'type' => $type],
                    ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
                );
            }
        }

        return redirect()->route('dean_osa.activity.index')
                         ->with('success', 'Activity updated successfully.');
    }

    public function destroy(string $id)
    {
        Activity::destroy($id);
        return redirect()->route('dean_osa.activity.index')
                         ->with('success', 'Activity deleted successfully.');
    }

    private function nextSequenceForSchoolYear(string $schoolYearCode, bool $lock = false): int
    {
        $query = Activity::where('school_year_code', $schoolYearCode)
            ->orWhere('code', 'like', $schoolYearCode . 's%');

        if ($lock) {
            $query->lockForUpdate();
        }

        $codes       = $query->pluck('code');
        $maxSequence = 0;

        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($schoolYearCode, '/') . 's(\d{4})$/', $code, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return $maxSequence + 1;
    }
}
