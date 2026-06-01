<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $departments = Department::query()
            ->with('branch')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($branchQuery) use ($search) {
                            $branchQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Dean_OSA.department.index', compact('departments'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();

        return view('Dean_OSA.department.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $validated['code'] = $validated['code'] ?: $this->makeCode($validated['branch_id'], $validated['name']);
        $this->validateCodeIsUnique($validated['code']);

        $department = Department::create($validated);

        SystemLog::record('Created Department', 'Department', [
            'subject_type' => Department::class,
            'subject_id' => $department->id,
            'subject_label' => $department->code,
            'description' => "Department {$department->name} ({$department->code}) was created.",
        ]);

        return redirect()->route('dean_osa.department.index')->with('success', 'Department created successfully.');
    }

    public function edit(string $id)
    {
        $department = Department::with('branch')->findOrFail($id);

        return view('Dean_OSA.department.edit', compact('department'));
    }

    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $validated['code'] = $validated['code'] ?: $this->makeCode($validated['branch_id'], $validated['name']);
        $this->validateCodeIsUnique($validated['code'], $department->id);
        $department->update($validated);

        SystemLog::record('Updated Department', 'Department', [
            'subject_type' => Department::class,
            'subject_id' => $department->id,
            'subject_label' => $department->code,
            'description' => "Department {$department->name} ({$department->code}) was updated.",
        ]);

        return redirect()->route('dean_osa.department.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        $name = $department->name;
        $code = $department->code;
        $department->delete();

        SystemLog::record('Deleted Department', 'Department', [
            'subject_type' => Department::class,
            'subject_id' => $id,
            'subject_label' => $code,
            'description' => "Department {$name} ({$code}) was deleted.",
        ]);

        return redirect()->route('dean_osa.department.index')->with('success', 'Department deleted successfully.');
    }

    public function byBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        return Department::query()
            ->where('branch_id', $request->query('branch_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    private function makeCode(int|string $branchId, string $name): string
    {
        $branchCode = Branch::find($branchId)?->code;
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');

        return $branchCode ? "{$branchCode}-{$initials}" : $initials;
    }

    private function validateCodeIsUnique(?string $code, ?int $ignoreId = null): void
    {
        if (! $code) {
            return;
        }

        $exists = Department::query()
            ->where('code', $code)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'The department code has already been taken.',
            ]);
        }
    }
}
