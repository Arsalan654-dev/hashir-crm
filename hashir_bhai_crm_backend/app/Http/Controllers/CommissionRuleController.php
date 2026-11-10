<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommissionRuleController extends Controller
{
    public function index(): View
    {
        return view('pages.hr.commission-rules', [
            'rules' => CommissionRule::query()
                ->with(['user:id,name', 'product:id,name', 'category:id,name'])
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'employees' => User::query()->whereNotNull('role_id')->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRule($request);

        $rule = CommissionRule::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        Activity::log('created', 'Commission rule created: '.$rule->name, $rule, [], 'payroll');

        return back()->with('status', 'Commission rule created.');
    }

    public function update(Request $request, CommissionRule $commissionRule): RedirectResponse
    {
        $data = $this->validateRule($request);

        $commissionRule->update($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', 'Commission rule updated.');
    }

    public function destroy(CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->delete();

        return back()->with('status', 'Commission rule deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRule(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'basis' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'applies_to' => ['required', 'in:all,product,category,user'],
            'product_id' => ['nullable', 'exists:products,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'min_sale_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
