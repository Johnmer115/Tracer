<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;
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
              ->whereIn('status', ['pending', 'for revision', 'for reschedule', 'reshedule'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title',  'like', "%{$search}%")
                          ->orWhere('code',   'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%");
                });
            });

        SarfListFilters::apply($query, $filters, ['pending', 'for revision', 'for reschedule', 'reshedule']);

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
        $departments = Department::with('branch')->orderBy('name')->get();
        $organizations = Organization::with('department.branch')->orderBy('name')->get();
        $levels = [
            'Elementary',
            'Junior High School',
            'Senior High School',
            'College/ETEEAP',
            'Graduate School',
            'All Levels',
            'Basic Education',
        ];
        $activeSchoolYear = SchoolYear::current();
        $nextSequence = $activeSchoolYear
            ? $this->nextSequenceForSchoolYear($activeSchoolYear->code)
            : null;

        return view('Dean_OSA.activity.create', compact(
            'branches',
            'departments',
            'organizations',
            'levels',
            'activeSchoolYear',
            'nextSequence',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'branch_id'          => 'nullable|exists:branches,id',
            'level'              => 'required|array|min:1',
            'level.*'            => 'string|max:255',
            'department'         => 'nullable|array',
            'department.*'       => 'string|max:255',
            'organizations'      => 'nullable|array',
            'organizations.*'    => 'string|max:255',
            'type_of_activity'   => 'required|in:Extra-Curricular,Co-Curricular',
            'event_type'         => 'required|in:Internal,External',
            'activity_level'     => 'required|in:Organization,Local,Interbranch,Off-Campus',
            'mode_of_conduct'    => 'required|in:Face to Face,Online,Hybrid',
            'date_of_activity'   => 'required|date',
            'time_start'         => 'nullable|required_with:time_end|date_format:H:i',
            'time_end'           => 'nullable|required_with:time_start|date_format:H:i|after:time_start',
            'funds'              => 'required|in:With Budget,ATC,No Fee',
            'amount'             => 'required_if:funds,With Budget,ATC|nullable|numeric|min:0',
            'types'              => 'nullable|array',
            'types.*'            => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
            'participants_count' => 'nullable|integer|min:0',
        ], [
            'time_start.required_with' => 'Please enter the activity start time.',
            'time_end.required_with'   => 'Please enter the activity end time.',
            'time_end.after'           => 'The activity end time must be after the start time.',
        ], [
            'time_start' => 'start time',
            'time_end'   => 'end time',
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
        $timeOfActivity = $this->formatActivityTimeRange($request);
        $departments = $this->cleanArrayInput($request->input('department', []));
        $organizations = $this->cleanArrayInput($request->input('organizations', []));

        $activity = DB::transaction(function () use ($request, $activeSchoolYear, $modeOfConduct, $hasVenue, $hasPlatform, $funds, $timeOfActivity, $departments, $organizations) {
            $lockedSchoolYear = SchoolYear::whereKey($activeSchoolYear->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence     = $this->nextSequenceForSchoolYear($lockedSchoolYear->code, true);
            $activityCode = $lockedSchoolYear->code . 's' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            return Activity::create([
                'code'                   => $activityCode,
                'school_year_code'       => $lockedSchoolYear->code,
                'branch_id'              => $request->input('branch_id'),
                'level'                  => $request->input('level', []),
                'department'             => $departments,
                'organizations'          => $organizations,
                'title'                  => $request->input('title'),
                'description'            => $request->input('description'),
                'objectives'             => $request->input('objectives', []),
                'type_of_activity'       => $request->input('type_of_activity'),
                'event_type'             => $request->input('event_type'),
                'activity_level'         => $request->input('activity_level'),
                'participants_profile'   => $request->input('participants_profile'),
                'participants_count'     => $request->input('participants_count'),
                'date_of_activity'       => $request->input('date_of_activity'),
                'time_of_activity'       => $timeOfActivity,
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
        $departments = Department::with('branch')->orderBy('name')->get();
        $organizations = Organization::with('department.branch')->orderBy('name')->get();

        return view('Dean_OSA.activity.edit', compact(
            'activity',
            'branches',
            'departments',
            'organizations'
        ));
    }

    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $isRescheduling = $activity->modification_type === 'rescheduling';

        if ($isRescheduling) {
            $request->validate([
                'mode_of_conduct'    => 'required|in:Face to Face,Online,Hybrid',
                'date_of_activity'   => 'required|date',
                'time_start'         => 'nullable|required_with:time_end|date_format:H:i',
                'time_end'           => 'nullable|required_with:time_start|date_format:H:i|after:time_start',
                'venue'              => 'nullable|string|max:255',
                'venue_type'         => 'nullable|in:On-Campus,Off-Campus',
                'platform'           => 'nullable|string|max:255',
            ], [
                'time_start.required_with' => 'Please enter the activity start time.',
                'time_end.required_with'   => 'Please enter the activity end time.',
                'time_end.after'           => 'The activity end time must be after the start time.',
            ], [
                'time_start' => 'start time',
                'time_end'   => 'end time',
            ]);

            $modeOfConduct = $request->input('mode_of_conduct');
            $hasVenue      = in_array($modeOfConduct, ['Face to Face', 'Hybrid'], true);
            $hasPlatform   = in_array($modeOfConduct, ['Online', 'Hybrid'], true);
            $timeOfActivity = $this->formatActivityTimeRange($request);
            $schedulePlace = $hasVenue
                ? $request->input('venue')
                : ($hasPlatform ? $request->input('platform') : null);

            $activity->update([
                'reschedule_status'       => 'pending',
                'reschedule_date'         => $request->input('date_of_activity'),
                'reschedule_time'         => $timeOfActivity,
                'reschedule_venue'        => $schedulePlace,
                'reschedule_reason'       => $activity->modification_remarks ?? 'Schedule modification requested.',
                'reschedule_remarks'      => null,
                'reschedule_requested_at' => now(),
                'reschedule_decided_at'   => null,
                'status'                  => 'for approval',
                'modification_type'       => null,
                'modification_remarks'    => null,
            ]);

            return redirect()->route('dean_osa.activity.index')
                             ->with('success', 'Rescheduling changes submitted. The current schedule stays unchanged until the new schedule is approved.');
        }

        $rules = [
            'title'              => 'required|string|max:255',
            'branch_id'          => 'nullable|exists:branches,id',
            'level'              => 'required|array|min:1',
            'level.*'            => 'string|max:255',
            'department'         => 'nullable|array',
            'department.*'       => 'string|max:255',
            'organizations'      => 'nullable|array',
            'organizations.*'    => 'string|max:255',
            'type_of_activity'   => 'required|in:Extra-Curricular,Co-Curricular',
            'event_type'         => 'required|in:Internal,External',
            'activity_level'     => 'required|in:Organization,Local,Interbranch,Off-Campus',
            'mode_of_conduct'    => 'required|in:Face to Face,Online,Hybrid',
            'date_of_activity'   => 'required|date',
            'time_start'         => 'nullable|required_with:time_end|date_format:H:i',
            'time_end'           => 'nullable|required_with:time_start|date_format:H:i|after:time_start',
            'types'              => 'nullable|array',
            'types.*'            => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
            'participants_count' => 'nullable|integer|min:0',
        ];

        $rules = array_merge($rules, [
            'funds'              => 'required|in:With Budget,ATC,No Fee',
            'amount'             => 'required_if:funds,With Budget,ATC|nullable|numeric|min:0',
        ]);

        $request->validate($rules, [
            'time_start.required_with' => 'Please enter the activity start time.',
            'time_end.required_with'   => 'Please enter the activity end time.',
            'time_end.after'           => 'The activity end time must be after the start time.',
        ], [
            'time_start' => 'start time',
            'time_end'   => 'end time',
        ]);

        $modeOfConduct = $request->input('mode_of_conduct');
        $hasVenue      = in_array($modeOfConduct, ['Face to Face', 'Hybrid'], true);
        $hasPlatform   = in_array($modeOfConduct, ['Online', 'Hybrid'], true);
        $funds         = $request->input('funds');
        $timeOfActivity = $this->formatActivityTimeRange($request);
        $departments = $this->cleanArrayInput($request->input('department', []));
        $organizations = $request->has('organizations')
            ? $this->cleanArrayInput($request->input('organizations', []))
            : ($activity->organizations ?? []);

        // When updating from 'for revision' or 'for reschedule', reset disapproved approvals to 'pending'
        $resetData = [];
        if (in_array($activity->status, ['for revision', 'for reschedule'])) {
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

        $updateData = [
            'branch_id'              => $request->input('branch_id'),
            'level'                  => $request->input('level', []),
            'department'             => $departments,
            'organizations'          => $organizations,
            'title'                  => $request->input('title'),
            'description'            => $request->input('description'),
            'objectives'             => $request->input('objectives', []),
            'type_of_activity'       => $request->input('type_of_activity'),
            'event_type'             => $request->input('event_type'),
            'activity_level'         => $request->input('activity_level'),
            'participants_profile'   => $request->input('participants_profile'),
            'participants_count'     => $request->input('participants_count'),
            'public_poster'          => $request->input('public_poster'),
            'mode_of_conduct'        => $modeOfConduct,
            'late_submission_reason' => $request->input('late_submission_reason'),
            // received_by / encoded_by intentionally not updated — keep original recorder
        ];

        $updateData = array_merge($updateData, [
            'date_of_activity'       => $request->input('date_of_activity'),
            'time_of_activity'       => $timeOfActivity,
            'venue'                  => $hasVenue    ? $request->input('venue')      : null,
            'venue_type'             => $hasVenue    ? $request->input('venue_type') : null,
            'platform'               => $hasPlatform ? $request->input('platform')   : null,
            'funds'                  => $funds,
            'source'                 => $funds === 'With Budget'                       ? $request->input('source')              : null,
            'amount'                 => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('amount')              : null,
            'expected_collection'    => $funds === 'ATC'                               ? $request->input('expected_collection') : null,
            'canteen'                => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('canteen')             : null,
            'procurement'            => in_array($funds, ['With Budget', 'ATC'], true) ? $request->input('procurement')         : null,
        ]);

        $updateData = array_merge($updateData, $resetData);

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

        // Clear modification fields after successful revision
        if ($activity->modification_type === 'revision') {
            Activity::where('id', $activity->id)->update([
                'modification_type'    => null,
                'modification_remarks' => null,
            ]);
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

    private function cleanArrayInput($values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $values), fn ($value) => filled($value))));
    }

    private function formatActivityTimeRange(Request $request): ?string
    {
        $start = $request->input('time_start');
        $end = $request->input('time_end');

        if (! $start && ! $end) {
            return null;
        }

        return date('g:i A', strtotime($start)) . ' - ' . date('g:i A', strtotime($end));
    }
}
