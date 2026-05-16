<?php

namespace App\Http\Controllers\Usertype;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DashboardMessage;
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

        // Dashboard messages — pinned first, then newest
        $messages = DashboardMessage::with('account')
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get();

        return view('Dean_OSA.dashboard.index', [
            'activities' => $paginatedActivities,
            'counts' => $counts,
            'filters' => $filters,
            'messages' => $messages,
            ...SarfListFilters::viewData(),
        ]);
    }

    /* ══════════════════════════════════════════════
       DASHBOARD MESSAGES
    ══════════════════════════════════════════════ */

    /**
     * Store a new dashboard message / remark.
     */
    public function storeMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'type'    => 'required|in:general,announcement,reminder',
        ]);

        DashboardMessage::create([
            'message'    => $request->input('message'),
            'type'       => $request->input('type'),
            'account_id' => auth()->id(),
        ]);

        return redirect()
            ->route('dean_osa.index')
            ->with('success', 'Message posted successfully.');
    }

    /**
     * Delete a dashboard message.
     */
    public function deleteMessage(string $id)
    {
        $message = DashboardMessage::findOrFail($id);
        $message->delete();

        return redirect()
            ->route('dean_osa.index')
            ->with('success', 'Message deleted.');
    }

    /**
     * Toggle pin status of a dashboard message.
     */
    public function togglePinMessage(string $id)
    {
        $message = DashboardMessage::findOrFail($id);
        $message->update(['is_pinned' => !$message->is_pinned]);

        return redirect()
            ->route('dean_osa.index')
            ->with('success', $message->is_pinned ? 'Message pinned.' : 'Message unpinned.');
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
