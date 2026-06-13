<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Support\SarfListFilters;
use App\Models\SystemLog;

class TracerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isBranchUser = auth()->user()?->usertype === 'Branch_OSA';
        $branchId = $isBranchUser ? auth()->user()?->branch_id : null;
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filters = SarfListFilters::fromRequest($request);
        $query = Activity::with(['branch', 'sarfDocuments'])
            ->when($isBranchUser, fn ($query) => $branchId
                ? $query->where('branch_id', $branchId)
                : $query->whereRaw('1 = 0'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            });

        SarfListFilters::apply($query, $filters);

        $filteredActivities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);
        $activities = SarfListFilters::paginateCollection($filteredActivities, $request, $perPage);

        return view('Dean_OSA.tracer.index', [
            'activities' => $activities,
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
        $isBranchUser = auth()->user()?->usertype === 'Branch_OSA';
        $branchId = $isBranchUser ? auth()->user()?->branch_id : null;

        abort_if($isBranchUser && ! $branchId, 404);

        $activity = Activity::with(['branch', 'sarfDocuments'])
            ->when($isBranchUser, fn ($query) => $query->where('branch_id', $branchId))
            ->findOrFail($id);

        $logs = \App\Models\SystemLog::with('account')
            ->where('subject_type', Activity::class)
            ->where('subject_id', $activity->id)
            ->latest()
            ->get();

        $routePrefix = $this->routePrefix();

        return view('Dean_OSA.tracer.view', compact('activity', 'routePrefix', 'logs'));
    }

    public function uploadDocument(Request $request, string $id)
    {
        $isBranchUser = auth()->user()?->usertype === 'Branch_OSA';
        $branchId = $isBranchUser ? auth()->user()?->branch_id : null;

        abort_if($isBranchUser && ! $branchId, 404);

        $activity = Activity::when($isBranchUser, fn ($query) => $query->where('branch_id', $branchId))
            ->findOrFail($id);

        $request->validate([
            'custom_document_names' => 'required|array|min:1',
            'custom_document_names.*' => 'required|string|max:120',
            'custom_document_files' => 'nullable|array',
            'custom_document_files.*' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $names = $request->input('custom_document_names', []);
        $files = $request->file('custom_document_files', []);

        \Illuminate\Support\Facades\DB::transaction(function () use ($activity, $names, $files) {
            foreach ($names as $index => $name) {
                $cleanName = preg_replace('/\s+/', ' ', trim($name));
                if ($cleanName === '') {
                    continue;
                }
                $type = 'OTHER:' . substr($cleanName, 0, 240);

                // Delete existing if same type exists
                $existing = \App\Models\SarfDocument::where('activity_id', $activity->id)
                    ->where('type', $type)
                    ->first();
                if ($existing) {
                    if ($existing->file_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existing->file_path);
                    }
                    $existing->delete();
                }

                if (isset($files[$index]) && $files[$index]->isValid()) {
                    $file = $files[$index];
                    $path = $file->store('sarf_documents', 'public');

                    \App\Models\SarfDocument::create([
                        'activity_id' => $activity->id,
                        'type' => $type,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                    ]);

                    SystemLog::record('Uploaded Document', 'Tracer', [
                        'subject_type' => Activity::class,
                        'subject_id' => $activity->id,
                        'subject_label' => $activity->title,
                        'description' => "Uploaded custom document: {$cleanName} ({$file->getClientOriginalName()})",
                    ]);
                } else {
                    \App\Models\SarfDocument::create([
                        'activity_id' => $activity->id,
                        'type' => $type,
                        'file_path' => null,
                        'original_filename' => null,
                    ]);

                    SystemLog::record('Added Document', 'Tracer', [
                        'subject_type' => Activity::class,
                        'subject_id' => $activity->id,
                        'subject_label' => $activity->title,
                        'description' => "Added custom document: {$cleanName} (No file attached)",
                    ]);
                }
            }
        });

        return redirect()->route($this->routeName('tracer.show'), $activity->id)
            ->with('success', 'Documents added successfully.');
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
