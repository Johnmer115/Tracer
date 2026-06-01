<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    // Level constant removed

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
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
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('Dean_OSA.orgs.index', compact('organizations'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('Dean_OSA.orgs.create', compact('branches', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $this->validateDepartmentBelongsToBranch($validated['department_id'], $validated['branch_id']);
        $validated['code'] = $validated['code'] ?: $this->makeCode($validated['department_id'], $validated['name']);
        $this->validateCodeIsUnique($validated['code']);
        unset($validated['branch_id']);

        $organization = Organization::create($validated);

        SystemLog::record('Created Organization', 'Organization', [
            'subject_type' => Organization::class,
            'subject_id' => $organization->id,
            'subject_label' => $organization->code,
            'description' => "Organization {$organization->name} ({$organization->code}) was created.",
        ]);

        return redirect()->route('dean_osa.orgs.index')->with('success', 'Organization created successfully.');
    }

    public function show(string $id)
    {
        return redirect()->route('dean_osa.orgs.edit', $id);
    }

    public function edit(string $id)
    {
        $organization = Organization::with('department.branch')->findOrFail($id);

        return view('Dean_OSA.orgs.edit', compact('organization'));
    }

    public function update(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $validated['code'] = $validated['code'] ?: $this->makeCode($organization->department_id, $validated['name']);
        $this->validateCodeIsUnique($validated['code'], $organization->id);
        $organization->update($validated);

        SystemLog::record('Updated Organization', 'Organization', [
            'subject_type' => Organization::class,
            'subject_id' => $organization->id,
            'subject_label' => $organization->code,
            'description' => "Organization {$organization->name} ({$organization->code}) was updated.",
        ]);

        return redirect()->route('dean_osa.orgs.index')->with('success', 'Organization updated successfully.');
    }

    public function destroy(string $id)
    {
        $organization = Organization::findOrFail($id);
        $name = $organization->name;
        $code = $organization->code;
        $organization->delete();

        SystemLog::record('Deleted Organization', 'Organization', [
            'subject_type' => Organization::class,
            'subject_id' => $id,
            'subject_label' => $code,
            'description' => "Organization {$name} ({$code}) was deleted.",
        ]);

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
