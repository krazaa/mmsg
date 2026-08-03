<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagementAuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 900;

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

        $this->ensureIsNotRateLimited($request, $credentials['email']);

        if (! Auth::attempt($credentials + ['status' => true], $request->boolean('remember'))) {
            $this->recordFailedAttempt($request, $credentials['email']);

            throw ValidationException::withMessages([
                'email' => 'The provided management credentials are incorrect.',
            ]);
        }

        if (! in_array($request->user()?->role, ['super_admin', 'admin'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->recordFailedAttempt($request, $credentials['email']);

            throw ValidationException::withMessages([
                'email' => 'This sign-in page is restricted to administrators.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $credentials['email']));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function ensureIsNotRateLimited(Request $request, string $email): void
    {
        $key = $this->throttleKey($request, $email);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => 'Too many sign-in attempts. Please try again in '.ceil($seconds / 60).' minutes.',
        ]);
    }

    private function recordFailedAttempt(Request $request, string $email): void
    {
        RateLimiter::hit($this->throttleKey($request, $email), self::LOCKOUT_SECONDS);
    }

    private function throttleKey(Request $request, string $email): string
    {
        return 'management-login:'.Str::transliterate(Str::lower($email).'|'.$request->ip());
    }
}
