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
        $query = Activity::with(['branch', 'sarfDocuments'])
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
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes($activity);

        return view('Dean_OSA.paar.view', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activity = Activity::with(['branch', 'sarfDocuments'])->findOrFail($id);
        $documents = $activity->sarfDocuments->keyBy('type');
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes($activity);

        return view('Dean_OSA.paar.edit', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    public function act(string $id)
    {
        $activity = Activity::with(['branch', 'sarfDocuments'])->findOrFail($id);

        if (! in_array($activity->status, ['approved', 'completed'], true)) {
            return redirect()
                ->route($this->routeName('paar.index'))
                ->withErrors(['status' => 'Only approved or completed activities can be opened for accomplishment.']);
        }

        $documents = $activity->sarfDocuments->keyBy('type');
        $accomplishmentDocuments = $this->accomplishmentDocumentTypes($activity);

        if ($this->hasAccomplishmentInput($documents, $accomplishmentDocuments)) {
            return redirect()->route($this->routeName('paar.edit'), $activity->id);
        }

        return view('Dean_OSA.paar.act', compact('activity', 'documents', 'accomplishmentDocuments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $documentTypes = $this->accomplishmentDocumentTypes($activity);

        if ($activity->status !== 'approved' && $activity->status !== 'completed') {
            return redirect()
                ->route($this->routeName('paar.index'))
                ->withErrors(['status' => 'Only approved activities can be moved to completed.']);
        }

        $existingDocuments = SarfDocument::where('activity_id', $activity->id)
            ->whereIn('type', array_keys($documentTypes))
            ->get()
            ->keyBy('type');

        $rules = [];
        foreach ($documentTypes as $type => $document) {
            $rules[$document['field']] = [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ];
            $rules[$document['field'] . '_hardcopy'] = 'nullable|boolean';
        }

        $request->validate($rules);

        foreach ($documentTypes as $type => $document) {
            $field = $document['field'];
            $hardcopyField = $field . '_hardcopy';

            if ($existingDocuments->has($type) || $request->hasFile($field) || $request->boolean($hardcopyField)) {
                continue;
            }

            return back()
                ->withErrors([$hardcopyField => "Upload {$document['label']} or check that its hardcopy is available."])
                ->withInput();
        }

        foreach ($documentTypes as $type => $document) {
            $field = $document['field'];
            $hardcopyField = $field . '_hardcopy';

            if (! $request->hasFile($field)) {
                if (! $existingDocuments->has($type) && $request->boolean($hardcopyField)) {
                    SarfDocument::create([
                        'activity_id' => $activity->id,
                        'type' => $type,
                        'file_path' => null,
                        'original_filename' => null,
                    ]);

                    SystemLog::record('Marked PAAR hardcopy available', 'PAAR', [
                        'subject_type' => Activity::class,
                        'subject_id' => $activity->id,
                        'subject_label' => $activity->code,
                        'description' => "Marked {$document['label']} hardcopy available for {$activity->code}.",
                    ]);
                }

                continue;
            }

            $file = $request->file($field);
            $path = $file->store('sarf_documents', 'public');

            $existingDocument = $existingDocuments->get($type);
            if ($existingDocument?->file_path) {
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

        $wasCompleted = $activity->status === 'completed';
        $activity->update(['status' => 'completed']);

        if ($wasCompleted) {
            SystemLog::record('Edited PAAR', 'PAAR', [
                'subject_type' => Activity::class,
                'subject_id' => $activity->id,
                'subject_label' => $activity->code,
                'description' => "PAAR accomplishment for {$activity->code} was edited.",
            ]);
        } else {
            SystemLog::record('Completed PAAR', 'PAAR', [
                'subject_type' => Activity::class,
                'subject_id' => $activity->id,
                'subject_label' => $activity->code,
                'description' => "{$activity->code} was marked as completed.",
            ]);
        }

        return redirect()
            ->route($this->routeName('paar.index'))
            ->with('success', 'Activity accomplishment completed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function accomplishmentDocumentTypes(?Activity $activity = null): array
    {
        $documents = [
            'PAAR_LIQUIDATION' => [
                'field' => 'liquidation_file',
                'code' => 'i',
                'label' => 'Liquidation',
            ],
            'PAAR_NARRATIVE_REPORT' => [
                'field' => 'narrative_report_file',
                'code' => 'ii',
                'label' => 'Narrative Report',
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

        if ($activity && ! $this->activityHasBudget($activity)) {
            unset($documents['PAAR_LIQUIDATION']);
            $romanCodes = ['i', 'ii', 'iii'];

            foreach (array_values(array_keys($documents)) as $index => $type) {
                $documents[$type]['code'] = $romanCodes[$index] ?? (string) ($index + 1);
            }
        }

        return $documents;
    }

    private function activityHasBudget(Activity $activity): bool
    {
        return in_array($activity->funds, ['With Budget', 'ATC'], true);
    }

    private function hasAccomplishmentInput($documents, array $accomplishmentDocuments): bool
    {
        foreach (array_keys($accomplishmentDocuments) as $type) {
            if ($documents->has($type)) {
                return true;
            }
        }

        return false;
    }
}
