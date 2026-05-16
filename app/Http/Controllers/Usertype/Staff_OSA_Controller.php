<?php

namespace App\Http\Controllers\Usertype;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\DashboardMessage;
use Illuminate\Pagination\LengthAwarePaginator;

class Staff_OSA_Controller extends Controller
{
    /**
     * Dashboard — overview of all activities with stat counts and filters.
     */
    public function index(Request $request)
    {
        $filters = [
            'branch_id'       => $request->query('branch_id', ''),
            'level'           => $request->query('level', ''),
            'pipeline_status' => $request->query('pipeline_status', ''),
            'inside_status'   => $request->query('inside_status', ''),
        ];

        $activities = Activity::with('branch')
            ->when($filters['branch_id'] !== '', fn($query) => $query->where('branch_id', $filters['branch_id']))
            ->when($filters['level'] !== '', fn($query) => $query->where('level', 'like', '%' . $filters['level'] . '%'))
            ->when($filters['pipeline_status'] !== '', function ($query) use ($filters) {
                match ($filters['pipeline_status']) {
                    'for approval' => $query->whereIn('status', ['for approval', 'for approval finance']),
                    default => $query->where('status', $filters['pipeline_status']),
                };
            })
            ->latest()
            ->get()
            ->map(function ($activity) {
                $activity->dashboard_inside_status = $this->approvalLocation($activity);
                return $activity;
            });

        if ($filters['inside_status'] !== '') {
            $activities = $activities
                ->filter(fn($activity) => $activity->dashboard_inside_status === $filters['inside_status'])
                ->values();
        }

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $paginatedActivities = new LengthAwarePaginator(
            $activities->forPage($page, $perPage)->values(),
            $activities->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $counts = [
            'total'        => $activities->count(),
            'pending'      => $activities->where('status', 'pending')->count(),
            'for_approval' => $activities->whereIn('status', ['for approval', 'for approval finance'])->count(),
            'approved'     => $activities->where('status', 'approved')->count(),
            'completed'    => $activities->where('status', 'completed')->count(),
        ];

        $branches = Branch::orderBy('name')->get();
        $levels   = Activity::query()
            ->pluck('level')
            ->flatMap(fn($level) => is_array($level) ? $level : (filled($level) ? [$level] : []))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $insideStatuses = collect($this->insideStatusOptions());

        // Dashboard messages — shared across all user types
        $messages = DashboardMessage::with('account')
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get();

        return view('Staff_OSA.dashboard.index', [
            'activities'     => $paginatedActivities,
            'counts'         => $counts,
            'branches'       => $branches,
            'levels'         => $levels,
            'insideStatuses' => $insideStatuses,
            'filters'        => $filters,
            'messages'       => $messages,
        ]);
    }

    /**
     * Activities — list all activities with search and pagination.
     */
    public function activityIndex(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with(['sarfDocuments', 'branch'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title',  'like', "%{$search}%")
                          ->orWhere('code',   'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Staff_OSA.activity.index', compact('activities'));
    }

    /**
     * Activity show — read-only view of a single activity.
     */
    public function activityShow(string $id)
    {
        $activity = Activity::with([
            'sarfDocuments',
            'branch',
            'receivedBy',
            'encodedBy',
        ])->findOrFail($id);

        return view('Staff_OSA.activity.show', compact('activity'));
    }

    /**
     * Approval — list activities that are in approval pipeline.
     */
    public function approvalIndex(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with('branch')
            ->whereIn('status', ['for approval', 'for approval finance'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                          ->orWhere('code',  'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Staff_OSA.approval.index', compact('activities'));
    }

    /**
     * PAAR — list activities that are approved or completed (post-activity).
     */
    public function paarIndex(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with('branch')
            ->whereIn('status', ['approved', 'completed'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                          ->orWhere('code',  'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Staff_OSA.paar.index', compact('activities'));
    }

    /* ── Private helpers (same as Dean) ── */

    private function approvalLocation(Activity $activity): ?string
    {
        if (! in_array($activity->status, ['for approval', 'for approval finance'], true)) {
            return null;
        }

        foreach ($this->applicableApprovalFields($activity) as $field => $label) {
            if (($activity->{$field} ?? 'pending') !== 'approved') {
                return $label;
            }
        }

        return null;
    }

    private function applicableApprovalFields(Activity $activity): array
    {
        $fields = [
            'approval_dean_sa' => 'Pending in OSA',
            'approval_avp_sps' => 'Pending in SPS',
        ];

        if ($this->requiresBasicEdApproval($activity)) {
            $fields['approval_dir_basic_ed'] = 'Pending in Basic Ed';
        }

        $fields += [
            'approval_vp_acad'     => 'Pending in Acad',
            'approval_vp_hrd_legal' => 'Pending in Legal',
        ];

        if ($activity->funds === 'With Budget') {
            $fields += [
                'approval_auditing'            => 'Pending in Auditing',
                'approval_comptroller_initial'  => 'Pending in Comptroller 1',
                'approval_finance_initial'      => 'Pending in Finance 1',
                'approval_osa_finance'          => 'Pending in OSA Finance',
                'approval_finance_final'        => 'Pending in Finance 2',
                'approval_comptroller_final'    => 'Pending in Comptroller 2',
            ];
        }

        return $fields;
    }

    private function insideStatusOptions(): array
    {
        return [
            'Pending in OSA',
            'Pending in SPS',
            'Pending in Basic Ed',
            'Pending in Acad',
            'Pending in Legal',
            'Pending in Auditing',
            'Pending in Comptroller 1',
            'Pending in Finance 1',
            'Pending in OSA Finance',
            'Pending in Finance 2',
            'Pending in Comptroller 2',
        ];
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
}
