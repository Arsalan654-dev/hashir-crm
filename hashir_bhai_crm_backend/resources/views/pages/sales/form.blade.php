@extends('layouts.app')

@php
    $isEdit = $sale->exists;
@endphp

@section('title', $isEdit ? 'Edit Sale' : 'New Sale')
@section('page-title', $isEdit ? 'Edit Sale — '.$sale->sale_no : 'New Sale Entry')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/sales') }}">Sales</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    @php
        $defaultItem = ['product_id' => '', 'name' => '', 'quantity' => 1, 'unit_price' => 0, 'cost' => 0, 'discount' => 0];
        $itemRows = old('items', $isEdit
            ? $sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'cost' => (float) $item->cost,
                'discount' => (float) $item->discount,
            ])->all()
            : [$defaultItem]);
        $itemRows = array_values($itemRows);
        $currencies = array_values(array_unique(array_merge(['USD', 'EUR', 'GBP', 'AED', 'SAR', 'PKR', 'INR'], [$sale->currency ?? 'USD'])));
        $productData = $products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'cost' => (float) $product->cost,
        ])->values();
    @endphp

    <form action="{{ $isEdit ? url('/sales/'.$sale->id) : url('/sales') }}" method="POST">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Customer</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" required value="{{ old('customer_name', $sale->customer_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Existing Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">None / new customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id', $sale->customer_id) == $customer->id)>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $sale->customer_phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $sale->customer_email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Related Lead</label>
                                <select name="lead_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($leads as $lead)
                                        <option value="{{ $lead->id }}" @selected(old('lead_id', $sale->lead_id) == $lead->id)>{{ $lead->lead_no }} — {{ $lead->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $sale->title) }}" placeholder="Short summary of the sale">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $sale->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Line Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-item"><i class="bx bx-plus me-1"></i> Add Item</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 200px;">Product</th>
                                        <th style="min-width: 160px;">Name</th>
                                        <th style="width: 100px;">Qty</th>
                                        <th style="width: 120px;">Unit Price</th>
                                        <th style="width: 110px;">Cost</th>
                                        <th style="width: 110px;">Discount</th>
                                        <th class="text-end" style="width: 110px;">Total</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    @foreach ($itemRows as $index => $item)
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-select item-product" data-selected="{{ $item['product_id'] ?? '' }}">
                                                    <option value="">Custom item</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="items[{{ $index }}][name]" class="form-control item-name" value="{{ $item['name'] ?? '' }}"></td>
                                            <td><input type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $item['quantity'] ?? 1 }}" required></td>
                                            <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" required></td>
                                            <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][cost]" class="form-control item-cost" value="{{ $item['cost'] ?? 0 }}"></td>
                                            <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][discount]" class="form-control item-discount" value="{{ $item['discount'] ?? 0 }}"></td>
                                            <td class="text-end fw-semibold item-total">{{ \App\Support\Money::format(max(0, ((float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0)) - (float) ($item['discount'] ?? 0))) }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger item-remove"><i class="bx bx-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <template id="sale-item-template">
                    <tr class="item-row">
                        <td>
                            <select name="items[__INDEX__][product_id]" class="form-select item-product" data-selected="">
                                <option value="">Custom item</option>
                            </select>
                        </td>
                        <td><input type="text" name="items[__INDEX__][name]" class="form-control item-name" value=""></td>
                        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" class="form-control item-quantity" value="1" required></td>
                        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" class="form-control item-price" value="0" required></td>
                        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][cost]" class="form-control item-cost" value="0"></td>
                        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount]" class="form-control item-discount" value="0"></td>
                        <td class="text-end fw-semibold item-total">{{ \App\Support\Money::format(0) }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger item-remove"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                </template>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Notes</h5></div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $sale->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Sale Details</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Salesperson</label>
                            <select name="salesperson_id" class="form-select">
                                @foreach ($salespeople as $salesperson)
                                    <option value="{{ $salesperson->id }}" @selected(old('salesperson_id', $sale->salesperson_id ?? auth()->id()) == $salesperson->id)>{{ $salesperson->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control" required value="{{ old('sale_date', $sale->sale_date?->toDateString()) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select">
                                @foreach (\App\Models\Sale::SOURCES as $source)
                                    <option value="{{ $source }}" @selected(old('source', $sale->source ?? 'manual') === $source)>{{ ucwords(str_replace('_', ' ', $source)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency }}" @selected(old('currency', $sale->currency ?? 'USD') === $currency)>{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" min="0" id="sale-discount" name="discount" class="form-control" value="{{ old('discount', $sale->discount ?? 0) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax</label>
                            <input type="number" step="0.01" min="0" id="sale-tax" name="tax" class="form-control" value="{{ old('tax', $sale->tax ?? 0) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Total Preview</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-medium" id="preview-subtotal">{{ \App\Support\Money::format(0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span class="fw-medium text-danger" id="preview-discount">{{ \App\Support\Money::format(0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span class="fw-medium" id="preview-tax">{{ \App\Support\Money::format(0) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Total</span>
                            <h5 class="mb-0" id="preview-total">{{ \App\Support\Money::format(0) }}</h5>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ $isEdit ? url('/sales/'.$sale->id) : url('/sales') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Sale' : 'Create Sale' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var products = @json($productData);
            var currency = @json(\App\Support\Branding::currencySymbol());
            var itemIndex = {{ count($itemRows) }};
            var body = document.getElementById('items-body');

            function format(value) {
                return currency + (Math.round(value * 100) / 100).toFixed(2);
            }

            function recalc() {
                var subtotal = 0;

                document.querySelectorAll('.item-row').forEach(function (row) {
                    var quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    var price = parseFloat(row.querySelector('.item-price').value) || 0;
                    var discount = parseFloat(row.querySelector('.item-discount').value) || 0;
                    var total = Math.max(0, quantity * price - discount);
                    row.querySelector('.item-total').textContent = format(total);
                    subtotal += total;
                });

                var discount = parseFloat(document.getElementById('sale-discount').value) || 0;
                var tax = parseFloat(document.getElementById('sale-tax').value) || 0;
                var total = Math.max(0, subtotal - discount + tax);

                document.getElementById('preview-subtotal').textContent = format(subtotal);
                document.getElementById('preview-discount').textContent = format(discount);
                document.getElementById('preview-tax').textContent = format(tax);
                document.getElementById('preview-total').textContent = format(total);
            }

            function populateProducts(row) {
                var select = row.querySelector('.item-product');
                var selected = select.getAttribute('data-selected') || select.value;

                select.innerHTML = '<option value="">Custom item</option>';

                products.forEach(function (product) {
                    var option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name + ' (' + format(product.price) + ')';
                    option.setAttribute('data-name', product.name);
                    option.setAttribute('data-price', product.price);
                    option.setAttribute('data-cost', product.cost);
                    select.appendChild(option);
                });

                select.value = selected || '';
            }

            function bindRow(row) {
                var select = row.querySelector('.item-product');

                select.addEventListener('change', function () {
                    var option = select.options[select.selectedIndex];

                    if (option && option.value) {
                        row.querySelector('.item-name').value = option.getAttribute('data-name');
                        row.querySelector('.item-price').value = option.getAttribute('data-price');
                        row.querySelector('.item-cost').value = option.getAttribute('data-cost');
                    }

                    recalc();
                });

                row.querySelectorAll('input').forEach(function (input) {
                    input.addEventListener('input', recalc);
                });
            }

            document.querySelectorAll('.item-row').forEach(function (row) {
                populateProducts(row);
                bindRow(row);
            });

            document.getElementById('add-item').addEventListener('click', function () {
                var markup = document.getElementById('sale-item-template').innerHTML.replace(/__INDEX__/g, itemIndex);
                itemIndex++;

                var holder = document.createElement('tbody');
                holder.innerHTML = markup;
                var row = holder.firstElementChild;

                body.appendChild(row);
                populateProducts(row);
                bindRow(row);
                recalc();
            });

            body.addEventListener('click', function (event) {
                var button = event.target.closest('.item-remove');

                if (!button) {
                    return;
                }

                if (document.querySelectorAll('.item-row').length <= 1) {
                    return;
                }

                button.closest('.item-row').remove();
                recalc();
            });

            document.getElementById('sale-discount').addEventListener('input', recalc);
            document.getElementById('sale-tax').addEventListener('input', recalc);

            recalc();
        });
    </script>
@endpush
