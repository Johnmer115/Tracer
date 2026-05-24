<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\SarfDocument;
use App\Models\SystemLog;
use App\Support\SarfListFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filters = SarfListFilters::fromRequest($request);
        $query = Activity::with('branch')
            ->whereIn('status', ['approved', 'completed'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });

        SarfListFilters::apply($query, $filters, ['completed', 'approved']);

        $filteredActivities = SarfListFilters::applyInsideStatus($query->latest()->get(), $filters);
        $activities = SarfListFilters::paginateCollection($filteredActivities, $request, $perPage);

        return view('Dean_OSA.paar.index', [
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
        return view('Dean_OSA.paar.create');
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
        $activity = Activity::with(['branch', 'sarfDocuments'])->findOrFail($id);
        $documents = $activity->sarfDocuments->keyBy('type');
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes();

        return view('Dean_OSA.paar.view', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activity = Activity::with(['branch', 'sarfDocuments'])->findOrFail($id);
        $documents = $activity->sarfDocuments->keyBy('type');
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes();

        return view('Dean_OSA.paar.edit', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    public function act(string $id)
    {
        $activity = Activity::with(['branch', 'sarfDocuments'])->findOrFail($id);

        if (! in_array($activity->status, ['approved', 'completed'], true)) {
            return redirect()
                ->route('dean_osa.paar.index')
                ->withErrors(['status' => 'Only approved or completed activities can be opened for accomplishment.']);
        }

        $documents = $activity->sarfDocuments->keyBy('type');
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes();

        return view('Dean_OSA.paar.act', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $documentTypes = $this->accomplishmentDocumentTypes();

        if ($activity->status !== 'approved' && $activity->status !== 'completed') {
            return redirect()
                ->route('dean_osa.paar.index')
                ->withErrors(['status' => 'Only approved activities can be moved to completed.']);
        }

        $existingDocuments = SarfDocument::where('activity_id', $activity->id)
            ->whereIn('type', array_keys($documentTypes))
            ->get()
            ->keyBy('type');

        $rules = [];
        foreach ($documentTypes as $type => $document) {
            $rules[$document['field']] = [
                $existingDocuments->has($type) ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ];
        }

        $request->validate($rules);

        foreach ($documentTypes as $type => $document) {
            if (! $request->hasFile($document['field'])) {
                continue;
            }

            $file = $request->file($document['field']);
            $path = $file->store('sarf_documents', 'public');

            $existingDocument = $existingDocuments->get($type);
            if ($existingDocument) {
                Storage::disk('public')->delete($existingDocument->file_path);
            }

            SarfDocument::updateOrCreate(
                ['activity_id' => $activity->id, 'type' => $type],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]
            );

            SystemLog::record('Uploaded PAAR document', 'PAAR', [
                'subject_type' => Activity::class,
                'subject_id' => $activity->id,
                'subject_label' => $activity->code,
                'description' => "Uploaded {$document['label']} for {$activity->code}.",
            ]);
        }

        $activity->update(['status' => 'completed']);

        SystemLog::record('Completed PAAR', 'PAAR', [
            'subject_type' => Activity::class,
            'subject_id' => $activity->id,
            'subject_label' => $activity->code,
            'description' => "{$activity->code} was marked as completed.",
        ]);

        return redirect()
            ->route('dean_osa.paar.index')
            ->with('success', 'Activity accomplishment completed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function accomplishmentDocumentTypes(): array
    {
        return [
            'PAAR_LIQUIDATION' => [
                'field' => 'liquidation_file',
                'code' => 'i',
                'label' => 'Liquadation',
            ],
            'PAAR_NARRATIVE_REPORT' => [
                'field' => 'narrative_report_file',
                'code' => 'ii',
                'label' => 'Narative Report',
            ],
            'PAAR_PHOTO_DOCUMENTS' => [
                'field' => 'photo_documents_file',
                'code' => 'iii',
                'label' => 'Photo Documentations',
            ],
            'PAAR_SUMMARY_REPORT' => [
                'field' => 'summary_report_file',
                'code' => 'iv',
                'label' => 'Summary Report of Accomplishment',
            ],
        ];
    }
}
