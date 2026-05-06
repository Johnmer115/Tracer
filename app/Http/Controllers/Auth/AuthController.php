<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
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

        Auth::login($account, $request->boolean('remember'));
        $request->session()->regenerate();

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
