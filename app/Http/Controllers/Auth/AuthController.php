<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = Account::query()
            ->where('username', $credentials['account'])
            ->first();

        if (! $account || ! $account->password || ! Hash::check($credentials['password'], $account->password)) {
            return back()->withErrors([
                'account' => 'The provided credentials do not match our records.',
            ])->onlyInput('account');
        }

        if (! in_array($account->usertype, ['Dean_OSA', 'Staff_OSA', 'Branch_OSA'], true)) {
            return back()->withErrors([
                'account' => 'This account has an invalid user type.',
            ])->onlyInput('account');
        }

        if ($account->status === 'inactive') {
            return back()->withErrors([
                'account' => 'This account is inactive.',
            ])->onlyInput('account');
        }

        Auth::login($account, $request->boolean('remember'));
        $request->session()->regenerate();

        SystemLog::record('Logged in', 'Authentication', [
            'account_id' => $account->id,
            'subject_type' => Account::class,
            'subject_id' => $account->id,
            'subject_label' => $account->username,
            'description' => $account->username . ' logged in as ' . $account->usertype . '.',
        ]);

        if ($account->usertype === 'Dean_OSA') {
            return redirect()->route('dean_osa.index');
        } 
        
        if ($account->usertype === 'Staff_OSA') {
            return redirect()->route('staff_osa.index');
        }

        if ($account->usertype === 'Branch_OSA'){
            return redirect()->route('branch_osa.index');
        }

        return redirect()->route('welcome');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request): RedirectResponse
    {
        $account = Auth::user();

        if ($account) {
            SystemLog::record('Logged out', 'Authentication', [
                'account_id' => $account->id,
                'subject_type' => Account::class,
                'subject_id' => $account->id,
                'subject_label' => $account->username,
                'description' => $account->username . ' logged out.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
