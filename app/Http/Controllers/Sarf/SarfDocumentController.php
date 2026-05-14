<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\SarfDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SarfDocumentController extends Controller
{
    public function show(Request $request, SarfDocument $document)
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        if ($request->boolean('print')) {
            return view('sarf_documents.print', [
                'document' => $document,
                'url' => route($this->documentRouteName($request), $document),
            ]);
        }

        if ($request->boolean('download')) {
            return Storage::disk('public')->download(
                $document->file_path,
                $document->original_filename
            );
        }

        return Storage::disk('public')->response(
            $document->file_path,
            $document->original_filename
        );
    }

    public function printActivity(Request $request, Activity $activity)
    {
        $documents = $activity->sarfDocuments()
            ->orderBy('type')
            ->get()
            ->filter(fn ($document) => Storage::disk('public')->exists($document->file_path))
            ->values();

        abort_if($documents->isEmpty(), 404);

        $routeName = $this->documentRouteName($request);

        return view('sarf_documents.print_all', [
            'activity' => $activity,
            'documents' => $documents,
            'routeName' => $routeName,
        ]);
    }

    private function documentRouteName(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? 'dean_osa.sarf-documents.show';
        $prefix = str($routeName)->before('.sarf-documents.');

        return $prefix . '.sarf-documents.show';
    }
}
