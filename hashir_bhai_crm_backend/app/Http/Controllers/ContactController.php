<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('pages.contacts-list', [
            'contacts' => Contact::query()->orderBy('id')->paginate(10),
            'totalContacts' => Contact::query()->count(),
        ]);
    }

    public function grid(): View
    {
        return view('pages.contacts-grid', [
            'contacts' => Contact::query()->orderBy('id')->paginate(8),
            'totalContacts' => Contact::query()->count(),
        ]);
    }

    public function profile(): View
    {
        $contact = Contact::query()->orderBy('id')->first();

        return view('pages.contacts-profile', [
            'contact' => $contact,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'projects' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Contact::create($data + [
            'badge_class' => 'bg-success-subtle',
            'badge_text_class' => 'text-success',
            'avatar' => 'assets/images/users/avatar-'.random_int(1, 8).'.jpg',
            'projects' => $data['projects'] ?? 0,
        ]);

        return back()->with('status', 'Contact created.');
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'projects' => ['nullable', 'integer', 'min:0'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $contact->update($data);

        return back()->with('status', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return back()->with('status', 'Contact deleted.');
    }
}
