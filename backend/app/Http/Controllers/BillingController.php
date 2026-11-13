<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        return view('pages.billing.index', [
            'organization' => $organization,
            'payments' => $organization?->payments()->latest()->paginate(10) ?? collect(),
            'bankDetails' => (string) Setting::getValue('billing.bank_details', ''),
            'instructions' => (string) Setting::getValue('billing.instructions', ''),
            'methodLabels' => SubscriptionPayment::METHOD_LABELS,
            'methods' => SubscriptionPayment::METHODS,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        if ($organization === null) {
            return back()->withErrors(['billing' => 'You are not linked to an organization.']);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(SubscriptionPayment::METHODS)],
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $proofPath = $request->file('proof')?->store('subscription-proofs', 'public');

        $organization->payments()->create([
            'amount' => $data['amount'],
            'currency' => $organization->currency,
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'status' => 'pending',
            'period_start' => now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'proof_path' => $proofPath,
        ]);

        Activity::log('subscription_payment_submitted', 'Payment submitted by '.$organization->name.' for review', $organization, [], 'billing');

        return back()->with('status', 'Payment submitted. Our team will verify it and activate your account shortly.');
    }
}
