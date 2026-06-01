<?php

namespace App\Http\Controllers\Management;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\SystemLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class SchoolYearController extends Controller
{
    
  public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $schoolYears = SchoolYear::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['current', 'inactive'], true), function ($query) use ($status) {
                $query->where('is_current', $status === 'current');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Dean_OSA.schoolyear.index', compact('schoolYears'));
    }

    public function create()
    {
        return view('Dean_OSA.schoolyear.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|unique:school_years,name',
            'code'       => 'required|string|unique:school_years,code',
        ]);

        $schoolYear = SchoolYear::create($this->schoolYearPayload($request) + ['is_current' => false]);

        SystemLog::record('Created School Year', 'School Year', [
            'subject_type' => SchoolYear::class,
            'subject_id' => $schoolYear->id,
            'subject_label' => $schoolYear->code,
            'description' => "School Year {$schoolYear->name} ({$schoolYear->code}) was created.",
        ]);

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'School year created successfully.');
    }

    private function schoolYearPayload(Request $request): array
    {
        $payload = [
            'name'       => $request->name,
            'code'       => $request->code,
        ];

        if (Schema::hasColumn('school_years', 'start_date')) {
            $payload['start_date'] = $this->fallbackStartDate($request->name);
        }

        if (Schema::hasColumn('school_years', 'end_date')) {
            $payload['end_date'] = $this->fallbackEndDate($request->name);
        }

        return $payload;
    }

    private function fallbackStartDate(string $name): string
    {
        return preg_match('/(\d{4})/', $name, $matches)
            ? $matches[1] . '-01-01'
            : now()->format('Y-m-d');
    }

    private function fallbackEndDate(string $name): string
    {
        return preg_match('/\d{4}\D+(\d{4})/', $name, $matches)
            ? $matches[1] . '-12-31'
            : now()->format('Y-m-d');
    }

    public function show(string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);
        return view('Dean_OSA.schoolyear.show', compact('schoolYear'));
    }

    public function edit(string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);
        return view('Dean_OSA.schoolyear.edit', compact('schoolYear'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'       => 'required|string|unique:school_years,name,' . $id,
            'code'       => 'required|string|unique:school_years,code,' . $id,
        ]);

        $schoolYear = SchoolYear::findOrFail($id);
        $schoolYear->update($this->schoolYearPayload($request));

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'School year updated successfully.');
    }

    public function destroy(string $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);
        $name = $schoolYear->name;
        $code = $schoolYear->code;
        $schoolYear->delete();

        SystemLog::record('Deleted School Year', 'School Year', [
            'subject_type' => SchoolYear::class,
            'subject_id' => $id,
            'subject_label' => $code,
            'description' => "School Year {$name} ({$code}) was deleted.",
        ]);

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'School year deleted successfully.');
    }

    public function setCurrent(string $id)
    {
        // Remove current from all
        SchoolYear::where('is_current', true)->update(['is_current' => false]);

        // Set new current
        SchoolYear::findOrFail($id)->update(['is_current' => true]);

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'Current school year updated.');
    }
}
