<?php

namespace App\Services;

use App\Mail\OrganizationCredentialsMail;
use App\Models\Organization;
use App\Models\Role;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{organization: Organization, owner: User|null, password: string|null}
     */
    public function create(array $data, ?User $creator = null): array
    {
        return DB::transaction(function () use ($data, $creator) {
            $organization = Organization::query()->create([
                'name' => $data['name'],
                'slug' => Organization::generateSlug($data['name']),
                'business_type' => $data['business_type'] ?? 'general',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'website' => $data['website'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Dubai',
                'currency' => $data['currency'] ?? 'USD',
                'status' => 'active',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'plan_name' => $data['plan_name'] ?? null,
                'plan_amount' => $data['plan_amount'] ?? 0,
                'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
                'paid_until' => $data['paid_until'] ?? null,
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator?->id,
            ]);

            $owner = null;
            $password = null;

            if (filled($data['owner_email'] ?? null)) {
                $password = filled($data['owner_password'] ?? null)
                    ? (string) $data['owner_password']
                    : Str::password(12);

                $owner = $this->createOwner($organization, $data, $password);
            }

            Activity::log(
                'organization_created',
                'Organization created: '.$organization->name,
                $organization,
                ['business_type' => $organization->business_type],
                'organizations',
            );

            return ['organization' => $organization, 'owner' => $owner, 'password' => $password];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOwner(Organization $organization, array $data, string $password): User
    {
        $owner = User::query()->create([
            'name' => $data['owner_name'] ?? $organization->name.' Admin',
            'email' => $data['owner_email'],
            'username' => $this->uniqueUsername((string) ($data['owner_username'] ?? $data['owner_email'])),
            'phone' => $data['owner_phone'] ?? null,
            'password' => $password,
            'organization_id' => $organization->id,
            'role_id' => $this->defaultOwnerRoleId(),
            'designation' => 'Owner',
            'is_active' => true,
        ]);

        $organization->update(['owner_user_id' => $owner->id]);

        return $owner;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Organization $organization, array $data): Organization
    {
        $organization->update($data);

        Activity::log(
            'organization_updated',
            'Organization updated: '.$organization->name,
            $organization,
            ['changes' => array_keys($data)],
            'organizations',
        );

        return $organization->refresh();
    }

    public function sendCredentials(Organization $organization, User $owner, ?string $plainPassword = null): bool
    {
        if (blank($owner->email)) {
            return false;
        }

        try {
            Mail::to($owner->email)->send(new OrganizationCredentialsMail($organization, $owner, $plainPassword));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Unable to send organization credentials email', [
                'organization_id' => $organization->id,
                'owner_id' => $owner->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array{ok: bool, password: string}
     */
    public function resetOwnerPassword(Organization $organization): array
    {
        $owner = $organization->owner;

        if ($owner === null) {
            return ['ok' => false, 'password' => ''];
        }

        $password = Str::password(12);
        $owner->update(['password' => $password]);

        return [
            'ok' => $this->sendCredentials($organization, $owner, $password),
            'password' => $password,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Organization $organization, array $data, ?User $recorder = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($organization, $data, $recorder) {
            $paidAt = now();
            $periodStart = (string) ($data['period_start'] ?? $paidAt->toDateString());
            $periodEnd = (string) ($data['period_end'] ?? $this->cycleEnd($periodStart, (string) $organization->billing_cycle));

            $payment = $organization->payments()->create([
                'amount' => $data['amount'] ?? $organization->plan_amount,
                'currency' => $data['currency'] ?? $organization->currency,
                'method' => $data['method'] ?? 'bank_transfer',
                'reference' => $data['reference'] ?? null,
                'status' => $data['status'] ?? 'paid',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'paid_at' => ($data['status'] ?? 'paid') === 'paid' ? $paidAt : null,
                'recorded_by' => $recorder?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($payment->status === 'paid') {
                $organization->update([
                    'payment_status' => 'paid',
                    'paid_until' => $periodEnd,
                ]);
            } elseif ((float) $payment->amount > 0) {
                $organization->update(['payment_status' => 'partial']);
            }

            Activity::log(
                'subscription_payment_recorded',
                'Payment recorded for '.$organization->name.': '.number_format((float) $payment->amount, 2).' '.$payment->currency,
                $organization,
                ['payment_id' => $payment->id, 'status' => $payment->status],
                'organizations',
            );

            return $payment;
        });
    }

    public function markPaymentPaid(SubscriptionPayment $payment, ?User $recorder = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($payment, $recorder) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'recorded_by' => $payment->recorded_by ?? $recorder?->id,
            ]);

            $organization = $payment->organization;

            if ($organization !== null) {
                $organization->update([
                    'payment_status' => 'paid',
                    'paid_until' => $payment->period_end,
                ]);

                Activity::log(
                    'subscription_payment_approved',
                    'Payment approved for '.$organization->name,
                    $organization,
                    ['payment_id' => $payment->id],
                    'organizations',
                );
            }

            return $payment->refresh();
        });
    }

    public function setCrmBlocked(Organization $organization, bool $blocked): void
    {
        $organization->update(['crm_blocked' => $blocked]);

        Activity::log(
            $blocked ? 'organization_crm_blocked' : 'organization_crm_unblocked',
            ($blocked ? 'CRM blocked: ' : 'CRM unblocked: ').$organization->name,
            $organization,
            [],
            'organizations',
        );
    }

    public function setBotBlocked(Organization $organization, bool $blocked): void
    {
        $organization->update(['bot_blocked' => $blocked]);

        Activity::log(
            $blocked ? 'organization_bot_blocked' : 'organization_bot_unblocked',
            ($blocked ? 'Bot blocked: ' : 'Bot unblocked: ').$organization->name,
            $organization,
            [],
            'organizations',
        );
    }

    public function cycleEnd(string $start, string $cycle): string
    {
        $date = Carbon::parse($start);

        return match ($cycle) {
            'quarterly' => $date->addMonths(3)->toDateString(),
            'yearly' => $date->addYear()->toDateString(),
            'one_time' => $date->addYears(10)->toDateString(),
            default => $date->addMonth()->toDateString(),
        };
    }

    public function defaultOwnerRoleId(): ?int
    {
        $role = Role::query()->where('slug', Role::ORGANIZATION_ADMIN)->first();

        if ($role === null) {
            $role = Role::query()->create([
                'name' => 'Organization Admin',
                'slug' => Role::ORGANIZATION_ADMIN,
                'color' => 'primary',
                'is_system' => true,
                'is_active' => true,
            ]);
        }

        return $role->id;
    }

    public function uniqueUsername(string $source): string
    {
        $base = Str::of($source)->before('@')->slug('_')->limit(24, '')->value() ?: 'user';
        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.$counter++;
        }

        return $username;
    }
}
