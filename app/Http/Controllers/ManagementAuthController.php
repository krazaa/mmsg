<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagementAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (in_array($request->user()?->role, ['super_admin', 'admin'], true)) {
            return redirect()->route('dashboard');
        }

        return view('auth.management-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided management credentials are incorrect.',
            ]);
        }

        if (! in_array($request->user()?->role, ['super_admin', 'admin'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This sign-in page is restricted to administrators.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
