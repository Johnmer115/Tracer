<?php

namespace App\Http\Controllers\Usertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class Branch_OSA_Controller extends Controller
{
    /**
     * Dashboard — overview of activities for this user's designated branch.
     */
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $activities = Activity::with('branch')
            ->where('branch_id', $branchId)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $activity->dashboard_inside_status = $this->approvalLocation($activity);
                return $activity;
            });

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

        $branchName = auth()->user()->branch->name ?? 'Your Branch';

        return view('Branch_OSA.dashboard.index', [
            'activities' => $paginatedActivities,
            'counts'     => $counts,
            'branchName' => $branchName,
        ]);
    }

    /**
     * Tracer — list activities for this user's branch with search + pagination.
     */
    public function tracerIndex(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $search   = trim((string) $request->query('search', ''));
        $perPage  = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with('branch')
            ->where('branch_id', $branchId)
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

        $branchName = auth()->user()->branch->name ?? 'Your Branch';

        return view('Branch_OSA.tracer.index', compact('activities', 'branchName'));
    }

    /**
     * Tracer show — read-only view of a specific activity (scoped to branch).
     */
    public function tracerShow(string $id)
    {
        $branchId = auth()->user()->branch_id;

        $activity = Activity::with([
            'sarfDocuments',
            'branch',
            'receivedBy',
            'encodedBy',
        ])
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        return view('Branch_OSA.tracer.show', compact('activity'));
    }

    /* ── Private helpers ── */

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
