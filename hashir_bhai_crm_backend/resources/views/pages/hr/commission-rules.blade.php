@extends('layouts.app')

@section('title', 'Commission Rules')
@section('page-title', 'Commission Rules')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Commission Rules</li>
@endsection

@section('page-actions')
    @can('commission_rules.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cds-rule-create"><i class="bx bx-plus me-1"></i> New Rule</button>
    @endcan
@endsection

@php
    $appliesLabels = [
        'all' => 'All Sales',
        'product' => 'Product',
        'category' => 'Category',
        'user' => 'Employee',
    ];
@endphp

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">All Rules</h5>
            <span class="text-muted font-size-13">{{ $rules->count() }} rules</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Applies To</th>
                            <th>Target</th>
                            <th>Basis</th>
                            <th class="text-end">Value</th>
                            <th class="text-end">Min Sale</th>
                            <th>Valid Dates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rules as $rule)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $rule->name }}</span>
                                    @if ($rule->notes)
                                        <div class="text-muted font-size-12">{{ $rule->notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $appliesLabels[$rule->applies_to] ?? ucfirst($rule->applies_to) }}</td>
                                <td class="text-muted">
                                    @switch($rule->applies_to)
                                        @case('product')
                                            {{ $rule->product?->name ?? '—' }}
                                            @break
                                        @case('category')
                                            {{ $rule->category?->name ?? '—' }}
                                            @break
                                        @case('user')
                                            {{ $rule->user?->name ?? '—' }}
                                            @break
                                        @default
                                            All sales
                                    @endswitch
                                </td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($rule->basis) }}</span></td>
                                <td class="text-end fw-semibold">
                                    @if ($rule->basis === \App\Models\CommissionRule::BASIS_PERCENTAGE)
                                        {{ number_format((float) $rule->value, 2) }}%
                                    @else
                                        {{ \App\Support\Money::format($rule->value) }}
                                    @endif
                                </td>
                                <td class="text-end">{{ $rule->min_sale_amount ? \App\Support\Money::format($rule->min_sale_amount) : '—' }}</td>
                                <td class="text-muted font-size-13">
                                    {{ $rule->starts_on?->format('d M Y') ?? 'Any' }}
                                    <i class="bx bx-right-arrow-alt"></i>
                                    {{ $rule->ends_on?->format('d M Y') ?? 'Open' }}
                                </td>
                                <td>
                                    @if ($rule->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @can('commission_rules.manage')
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#cds-rule-edit"
                                            data-action="{{ url('/commission-rules/'.$rule->id) }}"
                                            data-name="{{ $rule->name }}"
                                            data-basis="{{ $rule->basis }}"
                                            data-value="{{ $rule->value }}"
                                            data-applies="{{ $rule->applies_to }}"
                                            data-product="{{ $rule->product_id }}"
                                            data-category="{{ $rule->category_id }}"
                                            data-user="{{ $rule->user_id }}"
                                            data-min="{{ $rule->min_sale_amount }}"
                                            data-starts="{{ $rule->starts_on?->toDateString() }}"
                                            data-ends="{{ $rule->ends_on?->toDateString() }}"
                                            data-active="{{ $rule->is_active ? '1' : '0' }}"
                                            data-notes="{{ $rule->notes }}">Edit</button>
                                        <form action="{{ url('/commission-rules/'.$rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this commission rule?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No commission rules defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('commission_rules.manage')
        <div class="modal fade" id="cds-rule-create" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ url('/commission-rules') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">New Commission Rule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required maxlength="150">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Basis <span class="text-danger">*</span></label>
                                    <select name="basis" class="form-select" required>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Value <span class="text-danger">*</span></label>
                                    <input type="number" name="value" step="0.01" min="0" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Applies To <span class="text-danger">*</span></label>
                                    <select name="applies_to" class="form-select cds-applies" required>
                                        @foreach ($appliesLabels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="product">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">Select product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="category">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="user">
                                    <label class="form-label">Employee</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">Select employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Minimum Sale Amount</label>
                                    <input type="number" name="min_sale_amount" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Starts On</label>
                                    <input type="date" name="starts_on" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ends On</label>
                                    <input type="date" name="ends_on" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="cds-rule-active" checked>
                                        <label class="form-check-label" for="cds-rule-active">Rule active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Create Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="cds-rule-edit" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form id="cds-rule-edit-form" action="{{ url('/commission-rules') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Commission Rule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required maxlength="150">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Basis <span class="text-danger">*</span></label>
                                    <select name="basis" class="form-select" required>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Value <span class="text-danger">*</span></label>
                                    <input type="number" name="value" step="0.01" min="0" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Applies To <span class="text-danger">*</span></label>
                                    <select name="applies_to" class="form-select cds-applies" required>
                                        @foreach ($appliesLabels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="product">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">Select product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="category">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-none" data-applies="user">
                                    <label class="form-label">Employee</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">Select employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Minimum Sale Amount</label>
                                    <input type="number" name="min_sale_amount" step="0.01" min="0" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Starts On</label>
                                    <input type="date" name="starts_on" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ends On</label>
                                    <input type="date" name="ends_on" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="cds-rule-edit-active">
                                        <label class="form-check-label" for="cds-rule-edit-active">Rule active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script>
        (function () {
            function syncApplies(form) {
                var select = form.querySelector('.cds-applies');
                if (!select) { return; }
                form.querySelectorAll('[data-applies]').forEach(function (wrap) {
                    wrap.classList.toggle('d-none', wrap.getAttribute('data-applies') !== select.value);
                });
            }

            document.querySelectorAll('form').forEach(function (form) {
                if (!form.querySelector('.cds-applies')) { return; }
                form.querySelector('.cds-applies').addEventListener('change', function () { syncApplies(form); });
                syncApplies(form);
            });

            var editModal = document.getElementById('cds-rule-edit');

            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var form = document.getElementById('cds-rule-edit-form');
                    form.setAttribute('action', button.getAttribute('data-action'));
                    form.querySelector('[name="name"]').value = button.getAttribute('data-name') || '';
                    form.querySelector('[name="basis"]').value = button.getAttribute('data-basis') || 'percentage';
                    form.querySelector('[name="value"]').value = button.getAttribute('data-value') || '';
                    form.querySelector('[name="applies_to"]').value = button.getAttribute('data-applies') || 'all';
                    form.querySelector('[name="product_id"]').value = button.getAttribute('data-product') || '';
                    form.querySelector('[name="category_id"]').value = button.getAttribute('data-category') || '';
                    form.querySelector('[name="user_id"]').value = button.getAttribute('data-user') || '';
                    form.querySelector('[name="min_sale_amount"]').value = button.getAttribute('data-min') || '';
                    form.querySelector('[name="starts_on"]').value = button.getAttribute('data-starts') || '';
                    form.querySelector('[name="ends_on"]').value = button.getAttribute('data-ends') || '';
                    form.querySelector('[name="is_active"]').checked = button.getAttribute('data-active') === '1';
                    form.querySelector('[name="notes"]').value = button.getAttribute('data-notes') || '';
                    syncApplies(form);
                });
            }
        })();
    </script>
@endpush
