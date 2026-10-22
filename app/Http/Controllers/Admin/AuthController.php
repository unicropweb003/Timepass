<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /** Show the admin login page. */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login', [
            'googleEnabled' => filled(config('services.google.client_id')),
        ]);
    }

    /** Handle email + password admin login. */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->is_admin) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an administrator account.',
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages(['email' => 'This account has been disabled.']);
        }

        // No "remember me", so the session ends after the idle timeout.
        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages(['email' => 'The password is incorrect.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /** Redirect to Google for admin sign-in. */
    public function redirectToGoogle()
    {
        if (blank(config('services.google.client_id'))) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Google login is not configured yet.']);
        }

        return Socialite::driver('google')->redirect();
    }

    /** Handle the callback from Google. */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }

        // Only existing administrators may sign in with Google.
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user || ! $user->is_admin) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'This Google account is not an administrator. Ask an admin to grant access first.',
            ]);
        }

        if ($user->is_blocked) {
            return redirect()->route('admin.login')->withErrors(['email' => 'This account has been disabled.']);
        }

        // Link the Google id / avatar on first use.
        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /** Log the admin out. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
