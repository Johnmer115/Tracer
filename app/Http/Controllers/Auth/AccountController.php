<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AccountController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('search')) {
            $query->where('username', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('usertype')) {
            $query->where('usertype', $request->usertype);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accounts = $query->latest()->paginate($request->get('per_page', 10))->withQueryString();

        return view('Dean_OSA.account.index', compact('accounts'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('Dean_OSA.account.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'      => 'required|max:255|unique:accounts,username',
            'usertype'      => 'required|in:Dean_OSA,Staff_OSA,Branch_OSA',
            'password'      => 'required|min:6',
        ]);

        if ($request->input('usertype') === 'Branch_OSA') {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
        }

        $account = new Account;
        $account->username     = $request->input('username');
        $account->usertype     = $request->input('usertype');
        $account->password     = bcrypt($request->input('password'));
        $account->status       = 'active';
        $account->branch_id    = $request->input('branch_id');
        $account->save();

        if ($request->input('usertype') === 'Branch_OSA' && $request->filled('branch_id')) {
            // Link existing branch to this account
            $branch = Branch::findOrFail($request->branch_id);
            $branch->update(['account_id' => $account->id]);
        }

        SystemLog::record('Created Account', 'Account', [
            'subject_type' => Account::class,
            'subject_id' => $account->id,
            'subject_label' => $account->username,
            'description' => "Account {$account->username} ({$account->usertype}) was created with status {$account->status}.",
        ]);

        return redirect()->route('dean_osa.account.index')
                        ->with('success', 'Account created successfully.');
    }

    public function show(string $id)
    {
        $account = Account::findOrFail($id);

        return view('Dean_OSA.account.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $account  = Account::findOrFail($id);
        $branches = Branch::all();

        return view('Dean_OSA.account.edit', compact('account', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'username' => 'required|max:255|unique:accounts,username,' . $id,
            'usertype' => 'required|in:Dean_OSA,Staff_OSA,Branch_OSA',
            'password' => 'nullable|min:6',
            'status'   => 'required|in:active,inactive',
        ]);

        if ($request->input('usertype') === 'Branch_OSA') {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
        }

        $account = Account::findOrFail($id);

        $account->username  = $request->input('username');
        $account->usertype  = $request->input('usertype');
        $account->branch_id = $request->input('branch_id');
        $account->status    = $request->input('status');

        if (filled($request->input('password'))) {
            $account->password = bcrypt($request->input('password'));
        }

        $account->save();

        // Update branch link when usertype is Branch_OSA
        if ($request->input('usertype') === 'Branch_OSA' && $request->filled('branch_id')) {
            $branch = Branch::findOrFail($request->branch_id);
            $branch->update(['account_id' => $account->id]);
        }

        SystemLog::record('Updated Account', 'Account', [
            'subject_type' => Account::class,
            'subject_id' => $account->id,
            'subject_label' => $account->username,
            'description' => "Account {$account->username} ({$account->usertype}) was updated. Status: {$account->status}.",
        ]);

        return redirect()->route('dean_osa.account.index')->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $account = Account::findOrFail($id);
        $username = $account->username;
        $account->delete();

        SystemLog::record('Deleted Account', 'Account', [
            'subject_type' => Account::class,
            'subject_id' => $id,
            'subject_label' => $username,
            'description' => "Account {$username} was deleted.",
        ]);

        return redirect()->route('dean_osa.account.index')->with('success', 'Account deleted successfully.');
    }
}
