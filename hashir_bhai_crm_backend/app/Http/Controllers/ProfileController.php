<?php

namespace App\Http\Controllers;

use App\Support\Activity;
use App\Support\ProfilePdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        return view('pages.profile', [
            'user' => $user->load(['assignedRole', 'department', 'designationRecord', 'manager']),
            'contact' => $user,
            'mySalesCount' => $user->sales()->count(),
            'myLeadsCount' => $user->leads()->count(),
        ]);
    }

    public function template(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        return view('pages.contacts-profile', [
            'user' => $user,
            'contact' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'designation' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],
            'bank_iban' => ['nullable', 'string', 'max:60'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'preferences' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data)->save();

        Activity::log('profile_updated', $user->name.' updated their profile.', $user, [], 'profile');

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        $request->validate([
            'avatar' => ['required', 'image', 'max:4096'],
        ]);

        $user->forceFill(['avatar' => $request->file('avatar')->store('avatars', 'public')])->save();

        Activity::log('avatar_updated', $user->name.' updated their profile picture.', $user, [], 'profile');

        return back()->with('status', 'Profile picture updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->forceFill(['password' => $data['password']])->save();

        Activity::log('password_changed', $user->name.' changed their password.', $user, [], 'profile');

        return back()->with('status', 'Password changed successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->string('password'), $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        if ($user->is_super_admin) {
            return back()->withErrors(['password' => 'The super admin account cannot be deleted.']);
        }

        Activity::log('account_deleted', $user->name.' deleted their account.', $user, [], 'profile');

        $user->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth-login')->with('status', 'Your account has been deleted.');
    }

    public function pdf(): Response|RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('auth-login');
        }

        $imagePath = null;

        if ($user->avatar) {
            $imagePath = str_starts_with((string) $user->avatar, 'storage/')
                ? storage_path('app/public/'.substr((string) $user->avatar, 8))
                : (str_starts_with((string) $user->avatar, 'assets/')
                    ? public_path($user->avatar)
                    : storage_path('app/public/'.$user->avatar));

            if (! is_file($imagePath)) {
                $imagePath = public_path('assets/images/users/avatar-3.jpg');
            }
        } else {
            $imagePath = public_path('assets/images/users/avatar-3.jpg');
        }

        $fields = [
            'Name' => $user->name,
            'Employee Code' => $user->employee_code ?? '-',
            'Username' => $user->username ?? '-',
            'Email' => $user->email,
            'Phone' => $user->phone ?? '-',
            'Designation' => $user->designation ?? '-',
            'Department' => $user->department?->name ?? '-',
            'Role' => $user->assignedRole?->name ?? 'Member',
            'Joining Date' => $user->joining_date?->format('d M Y') ?? '-',
            'Address' => $user->address ?? '-',
            'City' => $user->city ?? '-',
            'Country' => $user->country ?? '-',
            'Bio' => $user->bio ?? '-',
            'Status' => $user->is_active ? 'Active' : 'Inactive',
            'Exported At' => now()->format('d M Y H:i'),
        ];

        $pdf = app(ProfilePdf::class)->build($fields, is_file((string) $imagePath) ? $imagePath : null);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="profile-'.$user->id.'.pdf"',
        ]);
    }
}
