<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SarfListFilters
{
    public static function fromRequest(Request $request): array
    {
        $filters = [
            'branch_id' => $request->query('branch_id', ''),
            'level' => $request->query('level', []),
            'pipeline_status' => $request->query('pipeline_status', ''),
            'inside_status' => $request->query('inside_status', ''),
        ];

        if (is_string($filters['level'])) {
            $filters['level'] = filled($filters['level']) ? [$filters['level']] : [];
        }

        $filters['level'] = collect((array) $filters['level'])
            ->filter(fn ($level) => filled($level))
            ->values()
            ->all();

        return $filters;
    }

    public static function apply($query, array $filters, array $allowedStatuses = [])
    {
        return $query
            ->when($filters['branch_id'] !== '', fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->when($filters['pipeline_status'] !== '', function ($query) use ($filters, $allowedStatuses) {
                $status = $filters['pipeline_status'];

                if (! empty($allowedStatuses) && ! in_array($status, $allowedStatuses, true)) {
                    return;
                }

                if ($status === 'for approval') {
                    $query->whereIn('status', ['for approval', 'for approval finance']);
                    return;
                }

                $query->where('status', $status);
            });
    }

    public static function viewData(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(),
            'levels' => Activity::query()
                ->pluck('level')
                ->flatMap(fn ($level) => is_array($level) ? $level : (filled($level) ? [$level] : []))
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'insideStatuses' => collect(self::insideStatusOptions()),
        ];
    }

    public static function applyInsideStatus($activities, array $filters)
    {
        $activities = $activities->map(function ($activity) {
            $activity->dashboard_inside_status = self::approvalLocation($activity);

            return $activity;
        });

        if (! empty($filters['level'])) {
            $selectedLevels = collect($filters['level'])
                ->map(fn ($level) => (string) $level)
                ->all();

            $activities = $activities
                ->filter(fn ($activity) => self::activityMatchesAnyLevel($activity, $selectedLevels))
                ->values();
        }

        if ($filters['inside_status'] === '') {
            return $activities;
        }

        return $activities
            ->filter(fn ($activity) => $activity->dashboard_inside_status === $filters['inside_status'])
            ->values();
    }

    public static function activityMatchesAnyLevel(Activity $activity, array $selectedLevels): bool
    {
        $activityLevels = collect(is_array($activity->level)
            ? $activity->level
            : (filled($activity->level) ? [$activity->level] : []))
            ->map(fn ($level) => (string) $level)
            ->all();

        return count(array_intersect($selectedLevels, $activityLevels)) > 0;
    }

    public static function paginateCollection($items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public static function approvalLocation(Activity $activity): ?string
    {
        if (! in_array($activity->status, ['for approval', 'for approval finance'], true)) {
            return null;
        }

        foreach (self::applicableApprovalFields($activity) as $field => $label) {
            if (($activity->{$field} ?? 'pending') !== 'approved') {
                return $label;
            }
        }

        return null;
    }

    public static function insideStatusOptions(): array
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

    private static function applicableApprovalFields(Activity $activity): array
    {
        $fields = [
            'approval_dean_sa' => 'Pending in OSA',
            'approval_avp_sps' => 'Pending in SPS',
        ];

        if (self::requiresBasicEdApproval($activity)) {
            $fields['approval_dir_basic_ed'] = 'Pending in Basic Ed';
        }

        $fields += [
            'approval_vp_acad' => 'Pending in Acad',
            'approval_vp_hrd_legal' => 'Pending in Legal',
        ];

        if ($activity->funds === 'With Budget') {
            $fields += [
                'approval_auditing' => 'Pending in Auditing',
                'approval_comptroller_initial' => 'Pending in Comptroller 1',
                'approval_finance_initial' => 'Pending in Finance 1',
                'approval_osa_finance' => 'Pending in OSA Finance',
                'approval_finance_final' => 'Pending in Finance 2',
                'approval_comptroller_final' => 'Pending in Comptroller 2',
            ];
        }

        return $fields;
    }

    private static function requiresBasicEdApproval(Activity $activity): bool
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
