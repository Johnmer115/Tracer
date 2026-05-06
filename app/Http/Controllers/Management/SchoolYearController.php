<?php

namespace App\Http\Controllers\Management;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Http\Controllers\Controller;

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
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        SchoolYear::create([
            'name'       => $request->name,
            'code'       => $request->code,
            'is_current' => false,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'School year created successfully.');
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
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $schoolYear = SchoolYear::findOrFail($id);
        $schoolYear->update([
            'name'       => $request->name,
            'code'       => $request->code,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return redirect()->route('dean_osa.schoolyear.index')
                         ->with('success', 'School year updated successfully.');
    }

    public function destroy(string $id)
    {
        SchoolYear::destroy($id);
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
