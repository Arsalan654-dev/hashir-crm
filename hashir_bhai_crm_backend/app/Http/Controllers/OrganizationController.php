<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\SubscriptionPayment;
use App\Services\OrganizationService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function __construct(private readonly OrganizationService $organizations) {}

    public function index(Request $request): View
    {
        $organizations = Organization::query()
            ->with(['owner:id,name,email,username', 'payments' => fn ($query) => $query->latest()->limit(1)])
            ->withCount('users')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->query('search');
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->query('payment_status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.organizations.index', [
            'organizations' => $organizations,
            'businessTypes' => Organization::BUSINESS_TYPE_LABELS,
            'filters' => $request->only(['search', 'status', 'payment_status']),
            'stats' => [
                'total' => Organization::query()->count(),
                'active' => Organization::query()->where('status', 'active')->count(),
                'unpaid' => Organization::query()->where('payment_status', '!=', 'paid')->count(),
                'blocked' => Organization::query()->where(fn ($query) => $query->where('crm_blocked', true)->orWhere('status', '!=', 'active'))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.organizations.form', [
            'organization' => new Organization([
                'timezone' => 'Asia/Dubai',
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'payment_status' => 'unpaid',
                'business_type' => 'restaurant',
            ]),
            'owner' => null,
            'businessTypes' => Organization::BUSINESS_TYPE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $result = $this->organizations->create($data, $request->user());

        $emailSent = false;

        if ($result['owner'] !== null) {
            $emailSent = $this->organizations->sendCredentials($result['organization'], $result['owner'], $result['password']);
        }

        return redirect()
            ->route('organizations.show', $result['organization'])
            ->with('status', 'Organization created for '.$result['organization']->name.'.'.($emailSent ? ' Login credentials emailed to '.$result['owner']->email.'.' : ' Please share the credentials manually.'))
            ->with('generated_password', $result['password']);
    }

    public function show(Organization $organization): View
    {
        $organization->load([
            'owner:id,name,email,username,phone,avatar,last_login_at,is_active',
            'creator:id,name',
            'payments' => fn ($query) => $query->with('recorder:id,name')->latest(),
            'users' => fn ($query) => $query->with('assignedRole:id,name,color')->orderBy('name'),
        ]);

        return view('pages.organizations.show', [
            'organization' => $organization,
            'businessTypes' => Organization::BUSINESS_TYPE_LABELS,
            'methodLabels' => SubscriptionPayment::METHOD_LABELS,
        ]);
    }

    public function edit(Organization $organization): View
    {
        return view('pages.organizations.form', [
            'organization' => $organization,
            'owner' => $organization->owner,
            'businessTypes' => Organization::BUSINESS_TYPE_LABELS,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $data = $this->validated($request, $organization);

        $this->organizations->update($organization, collect($data)
            ->except(['owner_name', 'owner_email', 'owner_username', 'owner_phone', 'owner_password'])
            ->all());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organization updated.');
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        if ($organization->users()->whereKeyNot($request->user()->id)->exists()) {
            return back()->withErrors(['organization' => 'This organization still has users. Block it instead of deleting, or delete its users first.']);
        }

        Activity::log('organization_deleted', 'Organization deleted: '.$organization->name, null, ['organization_id' => $organization->id], 'organizations');

        $organization->delete();

        return redirect()->route('organizations.index')->with('status', 'Organization deleted.');
    }

    public function recordPayment(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(SubscriptionPayment::METHODS)],
            'reference' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['paid', 'pending'])],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->organizations->recordPayment($organization, $data, $request->user());

        return back()->with('status', $data['status'] === 'paid'
            ? 'Payment recorded. Organization is now active.'
            : 'Payment marked as pending.');
    }

    public function approvePayment(Request $request, Organization $organization, SubscriptionPayment $payment): RedirectResponse
    {
        abort_unless((int) $payment->organization_id === (int) $organization->id, 404);

        $this->organizations->markPaymentPaid($payment, $request->user());

        return back()->with('status', 'Payment approved. '.$organization->name.' is now active.');
    }

    public function toggleCrm(Request $request, Organization $organization): RedirectResponse
    {
        $this->organizations->setCrmBlocked($organization, ! $organization->crm_blocked);

        return back()->with('status', $organization->refresh()->crm_blocked
            ? 'CRM access blocked for '.$organization->name.'.'
            : 'CRM access restored for '.$organization->name.'.');
    }

    public function toggleBot(Request $request, Organization $organization): RedirectResponse
    {
        $this->organizations->setBotBlocked($organization, ! $organization->bot_blocked);

        return back()->with('status', $organization->refresh()->bot_blocked
            ? 'WhatsApp bot blocked for '.$organization->name.'.'
            : 'WhatsApp bot enabled for '.$organization->name.'.');
    }

    public function sendCredentials(Request $request, Organization $organization): RedirectResponse
    {
        $owner = $organization->owner;

        if ($owner === null) {
            return back()->withErrors(['organization' => 'This organization has no owner user yet.']);
        }

        $sent = $this->organizations->sendCredentials($organization, $owner);

        return $sent
            ? back()->with('status', 'Credentials email sent to '.$owner->email.'.')
            : back()->withErrors(['organization' => 'Unable to send the email. Check your email/SMTP settings.']);
    }

    public function resetOwnerPassword(Request $request, Organization $organization): RedirectResponse
    {
        $result = $this->organizations->resetOwnerPassword($organization);

        if (! $result['ok']) {
            return back()->withErrors(['organization' => 'Could not reset the owner password. Check your email/SMTP settings.']);
        }

        return back()
            ->with('status', 'Owner password reset and emailed to '.$organization->owner?->email.'.')
            ->with('generated_password', $result['password']);
    }

    public function impersonateOwner(Request $request, Organization $organization): RedirectResponse
    {
        $owner = $organization->owner;

        if ($owner === null) {
            return back()->withErrors(['organization' => 'This organization has no owner user yet.']);
        }

        Activity::log('impersonated', 'Opened '.$organization->name.' dashboard as owner', $organization, [], 'organizations');

        $request->session()->put('impersonator_id', Auth::id());
        Auth::login($owner);

        return redirect()->route('home')->with('status', 'You are now viewing the dashboard as '.$owner->name.'.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Organization $organization = null): array
    {
        $isCreate = $organization === null;
        $ownerUserId = $organization?->owner_user_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'business_type' => ['required', Rule::in(Organization::BUSINESS_TYPES)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'currency' => ['nullable', 'string', 'max:10'],
            'plan_name' => ['nullable', 'string', 'max:100'],
            'plan_amount' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(Organization::BILLING_CYCLES)],
            'paid_until' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'owner_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:150'],
            'owner_email' => [$isCreate ? 'required' : 'nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($ownerUserId)],
            'owner_username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($ownerUserId)],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'owner_password' => ['nullable', 'string', 'min:8', 'max:100'],
        ]);
    }
}
