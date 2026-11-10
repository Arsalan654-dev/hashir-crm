<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Setting;
use App\Models\User;
use App\Support\Activity;
use App\Support\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = $this->filteredQuery($request)
            ->with(['assignedUser:id,name,avatar', 'customer:id,name'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.leads.index', [
            'leads' => $leads,
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'counts' => $this->statusCounts($request),
            'filters' => $request->only(['search', 'status', 'source', 'assigned_to', 'priority']),
        ]);
    }

    public function pipeline(Request $request): View
    {
        $leads = $this->filteredQuery($request)
            ->with(['assignedUser:id,name,avatar'])
            ->orderByDesc('estimated_value')
            ->get()
            ->groupBy('status');

        return view('pages.leads.pipeline', [
            'columns' => $leads,
            'statuses' => Lead::STATUSES,
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assigned(Request $request): View
    {
        $leads = Lead::query()
            ->whereNull('assigned_to')
            ->orWhere('status', 'new')
            ->with('assignedUser:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('pages.leads.assigned', [
            'leads' => $leads,
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Daily working queue: overdue and today's follow-ups for the team.
     */
    public function today(Request $request): View
    {
        $user = $request->user();
        $ids = $user->teamMemberIds();

        $base = fn () => Lead::query()
            ->whereIn('assigned_to', $ids)
            ->whereNotIn('status', ['won', 'lost'])
            ->with(['assignedUser:id,name,avatar', 'customer:id,name']);

        return view('pages.leads.today', [
            'overdue' => $base()->whereNotNull('next_follow_up_at')->whereDate('next_follow_up_at', '<', today())->orderBy('next_follow_up_at')->get(),
            'dueToday' => $base()->whereDate('next_follow_up_at', today())->orderBy('next_follow_up_at')->get(),
            'noFollowUp' => $base()->whereNull('next_follow_up_at')->orderByDesc('created_at')->limit(50)->get(),
            'teamMembers' => User::query()->whereIn('id', $ids)->orderBy('name')->get(['id', 'name', 'avatar']),
            'canAssign' => $user->isSuperAdmin() || $user->can('leads.assign'),
        ]);
    }

    public function create(): View
    {
        return view('pages.leads.form', [
            'lead' => new Lead(['status' => 'new', 'source' => 'manual', 'priority' => 'medium', 'currency' => Setting::getValue('company.currency', 'USD')]),
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->orderBy('name')->limit(500)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLead($request);

        if (! $request->user()->can('leads.view_all') && ! $request->user()->isSuperAdmin()) {
            $data['assigned_to'] = $request->user()->id;
        }

        $lead = Lead::query()->create($data + [
            'lead_no' => Lead::generateNumber(),
            'created_by' => $request->user()->id,
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'type' => 'note',
            'title' => 'Lead created',
            'body' => 'Lead added via '.$lead->source.'.',
            'happened_at' => now(),
        ]);

        Activity::log('created', 'Lead created: '.$lead->name, $lead, [], 'leads');

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorizeView($lead);

        $lead->load(['assignedUser:id,name,avatar', 'customer', 'activities.user:id,name,avatar', 'sales', 'whatsappContact', 'botConversation']);

        return view('pages.leads.show', [
            'lead' => $lead,
            'salespeople' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeView($lead);

        $data = $this->validateLead($request);
        $previousStatus = $lead->status;

        $lead->update($data);

        if ($previousStatus !== $lead->status) {
            LeadActivity::query()->create([
                'lead_id' => $lead->id,
                'user_id' => $request->user()->id,
                'type' => 'status_change',
                'title' => 'Status changed',
                'body' => ucfirst($previousStatus).' → '.ucfirst($lead->status),
                'happened_at' => now(),
            ]);

            if ($lead->status === 'won' && $lead->converted_at === null) {
                $lead->update(['converted_at' => now()]);
            }
        }

        Activity::log('updated', 'Lead updated: '.$lead->name, $lead, [], 'leads');

        return back()->with('status', 'Lead updated.');
    }

    public function storeActivity(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeView($lead);

        $data = $request->validate([
            'type' => ['required', 'string', 'max:30'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'happened_at' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'title' => $data['title'] ?? ucfirst($data['type']),
            'body' => $data['body'],
            'happened_at' => $data['happened_at'] ?? now(),
        ]);

        $lead->update([
            'last_contacted_at' => now(),
            'next_follow_up_at' => $data['next_follow_up_at'] ?? $lead->next_follow_up_at,
        ]);

        return back()->with('status', 'Activity logged.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        if (! $this->canAssignTo($request->user(), (int) $data['assigned_to'])) {
            return back()->withErrors(['assigned_to' => 'You can only assign leads within your own team.']);
        }

        $lead->update(['assigned_to' => $data['assigned_to']]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'type' => 'note',
            'title' => 'Lead assigned',
            'body' => 'Assigned to '.$lead->fresh()->assignedUser?->name,
            'happened_at' => now(),
        ]);

        Activity::log('assigned', 'Lead assigned: '.$lead->name, $lead, [], 'leads');

        $assignee = User::query()->find($data['assigned_to']);

        if ($assignee !== null) {
            Notifier::send(
                $assignee,
                'New lead assigned: '.$lead->name,
                'Follow up with '.$lead->name.' ('.$lead->phone.')'.($lead->next_follow_up_at ? ' — follow-up '.$lead->next_follow_up_at->format('d M') : ''),
                route('leads.show', $lead),
                'bx bx-target-lock',
            );
        }

        return back()->with('status', 'Lead assigned.');
    }

    private function canAssignTo(User $user, int $assigneeId): bool
    {
        if ($user->isSuperAdmin() || $user->can('leads.view_all')) {
            return true;
        }

        return $user->teamMemberIds()->contains($assigneeId);
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeView($lead);

        $customer = $lead->customer;

        if ($customer === null) {
            $customer = Customer::query()->create([
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'whatsapp_number' => $lead->whatsapp_number,
                'city' => $lead->city,
                'country' => $lead->country,
                'assigned_to' => $lead->assigned_to,
                'joining_date' => now()->toDateString(),
                'notes' => 'Converted from lead '.$lead->lead_no,
            ]);
        }

        $lead->update([
            'customer_id' => $customer->id,
            'status' => 'won',
            'converted_at' => now(),
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'type' => 'status_change',
            'title' => 'Lead converted',
            'body' => 'Lead converted to customer #'.$customer->id.'.',
            'happened_at' => now(),
        ]);

        Activity::log('converted', 'Lead converted: '.$lead->name, $lead, [], 'leads');

        return redirect()->route('leads.show', $lead)->with('status', 'Lead converted to a customer.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Activity::log('deleted', 'Lead deleted: '.$lead->name, null, ['lead_id' => $lead->id], 'leads');
        $lead->delete();

        return redirect()->route('leads.index')->with('status', 'Lead deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request)->with('assignedUser:id,name')->orderByDesc('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Lead No', 'Name', 'Company', 'Phone', 'Email', 'Source', 'Status', 'Priority', 'Value', 'Assigned To', 'Next Follow Up', 'Created']);

            $query->chunk(500, function (Collection $leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->lead_no,
                        $lead->name,
                        $lead->company,
                        $lead->phone,
                        $lead->email,
                        $lead->source,
                        $lead->status,
                        $lead->priority,
                        number_format((float) $lead->estimated_value, 2, '.', ''),
                        $lead->assignedUser?->name,
                        $lead->next_follow_up_at?->toDateString(),
                        $lead->created_at->toDateString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'leads-export-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * @return Builder<Lead>
     */
    private function filteredQuery(Request $request): Builder
    {
        return Lead::query()
            ->forUser($request->user())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('company', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('lead_no', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->when($request->filled('assigned_to'), fn ($query) => $query->where('assigned_to', $request->integer('assigned_to')));
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(Request $request): array
    {
        $counts = $this->filteredQuery($request)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLead(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:30'],
            'priority' => ['nullable', 'string', 'max:20'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'message' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'product_interest' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'next_follow_up_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function authorizeView(Lead $lead): void
    {
        $user = auth()->user();

        if ($user === null) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->can('leads.view_all') || $lead->assigned_to === $user->id || $lead->created_by === $user->id) {
            return;
        }

        abort(403, '403 — You cannot access this.');
    }
}
