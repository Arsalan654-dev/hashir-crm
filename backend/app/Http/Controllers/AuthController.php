<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('pages.auth-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::query()->where($field, $credentials['username'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'These credentials do not match our records.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Your account has been deactivated. Please contact administrator.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_seen_at' => now(),
        ])->saveQuietly();

        Activity::log('login', $user->name.' signed in.', $user, [], 'auth');

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            Activity::log('logout', $user->name.' signed out.', $user, [], 'auth');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth-login');
    }

    public function showRecover(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('pages.auth-recoverpw');
    }

    public function recover(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::query()->where('email', $request->string('email'))->first();

        if ($user === null) {
            return redirect()->route('auth-recoverpw')->withErrors([
                'email' => 'No account found with that email address.',
            ]);
        }

        $token = Password::createToken($user);

        try {
            $user->sendPasswordResetNotification($token);
        } catch (\Throwable $e) {
            report($e);
        }

        $resetUrl = url('/auth-reset-password?email='.urlencode($user->email).'&token='.$token);

        Activity::log('password_reset_requested', $user->email.' requested a password reset.', $user, [], 'auth');

        return redirect()->route('auth-confirm-mail')
            ->with('status', 'Password reset link has been sent to your email.')
            ->with('reset_url', $resetUrl);
    }

    public function showReset(Request $request): View|RedirectResponse
    {
        $email = $request->query('email');
        $token = $request->query('token');

        if (! is_string($email) || ! is_string($token) || $email === '' || $token === '') {
            return redirect()->route('auth-recoverpw')->withErrors([
                'email' => 'Invalid or missing password reset link.',
            ]);
        }

        return view('pages.auth-reset-password', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            [
                'email' => $request->string('email'),
                'password' => $request->string('password'),
                'password_confirmation' => $request->string('password_confirmation'),
                'token' => $request->string('token'),
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
                Activity::log('password_reset', $user->email.' reset their password.', $user, [], 'auth');
                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Password has been reset successfully.');
    }

    public function showLock(): View
    {
        return view('pages.auth-lock-screen');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        if (! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password is incorrect.',
            ]);
        }

        return redirect()->route('home');
    }

    public function showTwoStep(): View
    {
        return view('pages.auth-two-step-verification');
    }

    public function twoStep(Request $request): RedirectResponse
    {
        $code = $request->input('digit1').$request->input('digit2').$request->input('digit3').$request->input('digit4');

        if (strlen((string) $code) !== 4) {
            throw ValidationException::withMessages([
                'code' => 'Please enter the 4 digit code.',
            ]);
        }

        return redirect()->route('home');
    }
}
