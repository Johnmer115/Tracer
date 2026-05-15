<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    private const LEVELS = [
        'Elementary',
        'Junior High School',
        'Senior High School',
        'College/ETEEAP',
        'Graduate School',
        'All Levels',
        'Basic Education',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $level = trim((string) $request->query('level', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $organizations = Organization::query()
            ->with(['department.branch', 'account'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('department', fn ($department) => $department->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('department.branch', fn ($branch) => $branch->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($level !== '', fn ($query) => $query->where('level', $level))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $levels = self::LEVELS;

        return view('Dean_OSA.orgs.index', compact('organizations', 'levels'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $levels = self::LEVELS;

        return view('Dean_OSA.orgs.create', compact('branches', 'departments', 'levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'level' => 'required|string|in:' . implode(',', self::LEVELS),
            'code' => 'nullable|string|max:255',
        ]);

        $this->validateDepartmentBelongsToBranch($validated['department_id'], $validated['branch_id']);
        $validated['code'] = $validated['code'] ?: $this->makeCode($validated['department_id'], $validated['name']);
        $this->validateCodeIsUnique($validated['code']);
        unset($validated['branch_id']);

        Organization::create($validated);

        return redirect()->route('dean_osa.orgs.index')->with('success', 'Organization created successfully.');
    }

    public function show(string $id)
    {
        return redirect()->route('dean_osa.orgs.edit', $id);
    }

    public function edit(string $id)
    {
        $organization = Organization::with('department.branch')->findOrFail($id);
        $levels = self::LEVELS;

        return view('Dean_OSA.orgs.edit', compact('organization', 'levels'));
    }

    public function update(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|in:' . implode(',', self::LEVELS),
            'code' => 'nullable|string|max:255',
        ]);

        $validated['code'] = $validated['code'] ?: $this->makeCode($organization->department_id, $validated['name']);
        $this->validateCodeIsUnique($validated['code'], $organization->id);
        $organization->update($validated);

        return redirect()->route('dean_osa.orgs.index')->with('success', 'Organization updated successfully.');
    }

    public function destroy(string $id)
    {
        Organization::findOrFail($id)->delete();

        return redirect()->route('dean_osa.orgs.index')->with('success', 'Organization deleted successfully.');
    }

    private function makeCode(int|string $departmentId, string $name): string
    {
        $departmentCode = Department::find($departmentId)?->code;
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');

        return $departmentCode ? "{$departmentCode}-{$initials}" : $initials;
    }

    private function validateDepartmentBelongsToBranch(int|string $departmentId, int|string $branchId): void
    {
        $matches = Department::query()
            ->whereKey($departmentId)
            ->where('branch_id', $branchId)
            ->exists();

        if (! $matches) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department does not belong to the selected branch.',
            ]);
        }
    }

    private function validateCodeIsUnique(?string $code, ?int $ignoreId = null): void
    {
        if (! $code) {
            return;
        }

        $exists = Organization::query()
            ->where('code', $code)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => 'The organization code has already been taken.',
            ]);
        }
    }
}
