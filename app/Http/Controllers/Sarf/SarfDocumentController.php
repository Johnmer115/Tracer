<?php

namespace App\Http\Controllers\Sarf;

use App\Http\Controllers\Controller;
use App\Models\SarfDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SarfDocumentController extends Controller
{
    public function show(Request $request, SarfDocument $document)
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

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
}
