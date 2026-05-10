<?php

namespace App\Http\Controllers\Usertype;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\SarfListFilters;

class Dean_OSA_Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = SarfListFilters::fromRequest($request);
        $query = Activity::with('branch');

        SarfListFilters::apply($query, $filters);

        $activities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);
        $perPage = 10;
        $paginatedActivities = SarfListFilters::paginateCollection($activities, $request, $perPage);

        $counts = [
            'total' => $activities->count(),
            'pending' => $activities->where('status', 'pending')->count(),
            'for_approval' => $activities->whereIn('status', ['for approval', 'for approval finance'])->count(),
            'approved' => $activities->where('status', 'approved')->count(),
            'completed' => $activities->where('status', 'completed')->count(),
        ];

        return view('Dean_OSA.dashboard.index', [
            'activities' => $paginatedActivities,
            'counts' => $counts,
            'filters' => $filters,
            ...SarfListFilters::viewData(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
