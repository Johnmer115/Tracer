<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\SarfListFilters;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->reportData($request);

        return view('Dean_OSA.report.index', [
            'activities' => SarfListFilters::paginateCollection($data['filteredActivities'], $request, $data['perPage']),
            'counts' => $data['counts'],
            'filters' => $data['filters'],
            'moduleFilters' => $data['moduleFilters'],
            ...SarfListFilters::viewData(),
        ]);
    }

    public function print(Request $request)
    {
        $data = $this->reportData($request);

        return view('Dean_OSA.report.print', [
            'activities' => $data['filteredActivities'],
            'counts' => $data['counts'],
            'filters' => $data['filters'],
            'moduleFilters' => $data['moduleFilters'],
            'printedAt' => now(),
            ...SarfListFilters::viewData(),
        ]);
    }

    private function reportData(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $moduleFilters = collect($request->query('modules', []))
            ->filter(fn($module) => in_array($module, ['activities', 'approvals', 'paar'], true))
            ->values()
            ->all();

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filters = SarfListFilters::fromRequest($request);
        $moduleStatuses = [
            'activities' => ['pending', 'for revision', 'for reschedule', 'for rescheduling', 'reshedule'],
            'approvals' => ['pending', 'ongoing', 'for approval', 'for approval finance', 'for approval for rescheduling'],
            'paar' => ['approved', 'completed'],
        ];

        $statuses = count($moduleFilters)
            ? collect($moduleFilters)
                ->flatMap(fn($module) => $moduleStatuses[$module])
                ->unique()
                ->values()
                ->all()
            : [
            'pending',
            'ongoing',
            'for approval',
            'for approval finance',
            'for approval for rescheduling',
            'for revision',
            'for reschedule',
            'for rescheduling',
            'reshedule',
            'approved',
            'completed',
            'cancelled',
        ];

        $query = Activity::with('branch')
            ->whereIn('status', $statuses)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });

        SarfListFilters::apply($query, $filters, $statuses);

        $filteredActivities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);

        $counts = [
            'total' => $filteredActivities->count(),
            'pending' => $filteredActivities->where('status', 'pending')->count(),
            'for_approval' => $filteredActivities
                ->whereIn('status', ['for approval', 'for approval finance'])
                ->count(),
            'rescheduling' => $filteredActivities
                ->whereIn('status', ['for reschedule', 'for rescheduling', 'reshedule', 'for approval for rescheduling'])
                ->count(),
            'approved' => $filteredActivities->where('status', 'approved')->count(),
            'completed' => $filteredActivities->where('status', 'completed')->count(),
        ];

        return [
            'filteredActivities' => $filteredActivities,
            'counts' => $counts,
            'filters' => $filters,
            'moduleFilters' => $moduleFilters,
            'perPage' => $perPage,
        ];
    }
}
