<?php

namespace App\Http\Controllers\Management;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $branches = Branch::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Dean_OSA.branch.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('Dean_OSA.branch.create');
    }

     public function store(Request $request)
    {
        //
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|string',
            'code'     => 'required|string|unique:branches,code',
        ]);

        $branch = new Branch;
        $branch->name = $request->input('name');
        $branch->location = $request->input('location');
        $branch->code = $request->input('code');
        $branch->save();

        SystemLog::record('Created Branch', 'Branch', [
            'subject_type' => Branch::class,
            'subject_id' => $branch->id,
            'subject_label' => $branch->code,
            'description' => "Branch {$branch->name} ({$branch->code}) was created.",
        ]);

        return redirect()->route('dean_osa.branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $branch = Branch::findOrFail($id);

        return view('Dean_OSA.branch.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $branch = Branch::findOrFail($id);

        return view('Dean_OSA.branch.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'location' => 'required',
            'code'     => 'required|string|unique:branches,code,' . $id,
        ]);

        $branch = Branch::findOrFail($id);
        $branch->name     = $request->name;
        $branch->location = $request->location;
        $branch->code     = $request->code;
        $branch->save();

        SystemLog::record('Updated Branch', 'Branch', [
            'subject_type' => Branch::class,
            'subject_id' => $branch->id,
            'subject_label' => $branch->code,
            'description' => "Branch {$branch->name} ({$branch->code}) was updated.",
        ]);

        return redirect()->route('dean_osa.branch.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $branch = Branch::findOrFail($id);
        $name = $branch->name;
        $code = $branch->code;
        $branch->delete();

        SystemLog::record('Deleted Branch', 'Branch', [
            'subject_type' => Branch::class,
            'subject_id' => $id,
            'subject_label' => $code,
            'description' => "Branch {$name} ({$code}) was deleted.",
        ]);

        return redirect()->route('dean_osa.branch.index')->with('success', 'Branch deleted successfully.');
    }
}
