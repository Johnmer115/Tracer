<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SarfDocument;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $activities = Activity::with('sarfDocuments')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name',   'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Dean_OSA.activity.index', compact('activities'));
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create()
    {
        return view('Dean_OSA.activity.create');
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'types'            => 'nullable|array',
            'types.*'          => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
        ]);

        $activity = Activity::create([
            'name'   => $request->input('name'),
            'status' => 'pending',
            'code'   => 'SARF-' . strtoupper(uniqid()),
        ]);

        // Handle file uploads
        foreach ($request->input('types', []) as $type) {
            $fileKey = 'file_' . $type;
            if ($request->hasFile($fileKey)) {
                $file     = $request->file($fileKey);
                $path     = $file->store('sarf_documents', 'public');
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
     * Display the specified activity.
     */
    public function show(string $id)
    {
        $activity = Activity::with('sarfDocuments')->findOrFail($id);

        return view('Dean_OSA.activity.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified activity.
     */
    public function edit(string $id)
    {
        $activity = Activity::with('sarfDocuments')->findOrFail($id);

        return view('Dean_OSA.activity.edit', compact('activity'));
    }

    /**
     * Update the specified activity.
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:255',
            'types'   => 'nullable|array',
            'types.*' => 'in:A0,A1,A2,A3,A4,A5,A6,A7,A8,A10',
        ]);

        $activity->update([
            'name' => $request->input('name'),
        ]);

        // Handle file uploads / replacements
        foreach ($request->input('types', []) as $type) {
            $fileKey = 'file_' . $type;
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store('sarf_documents', 'public');

                // Replace existing or create new
                SarfDocument::updateOrCreate(
                    ['activity_id' => $activity->id, 'type' => $type],
                    [
                        'file_path'         => $path,
                        'original_filename' => $file->getClientOriginalName(),
                    ]
                );
            }
        }

        return redirect()->route('dean_osa.activity.index')
                         ->with('success', 'Activity updated successfully.');
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(string $id)
    {
        Activity::destroy($id);

        return redirect()->route('dean_osa.activity.index')
                         ->with('success', 'Activity deleted successfully.');
    }
}
