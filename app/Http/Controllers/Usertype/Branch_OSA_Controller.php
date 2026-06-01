<?php

namespace App\Http\Controllers\Usertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\DashboardMessage;

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

        $counts = [
            'total'        => $activities->count(),
            'pending'      => $activities->where('status', 'pending')->count(),
            'for_approval' => $activities->whereIn('status', ['for approval', 'for approval finance'])->count(),
            'rescheduling' => $activities->whereIn('status', [
                'for reschedule',
                'for rescheduling',
                'reshedule',
                'for approval for rescheduling',
            ])->count(),
            'approved'     => $activities->where('status', 'approved')->count(),
            'completed'    => $activities->where('status', 'completed')->count(),
        ];

        $branchName = auth()->user()->branch->name ?? 'Your Branch';

        // Dashboard messages — shared across all user types
        $messages = DashboardMessage::with(['account', 'branch'])
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id')
                    ->when($branchId, fn ($query) => $query->orWhere('branch_id', $branchId));
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get();

        return view('Branch_OSA.dashboard.index', [
            'counts'     => $counts,
            'branchName' => $branchName,
            'messages'   => $messages,
        ]);
    }
    /* Private helpers */

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
            'approval_vp_acad' => 'Pending in Acad',
        ];

        if ($activity->waiver_consent === 'With') {
            $fields['approval_vp_hrd_legal'] = 'Pending in Legal';
        }

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
