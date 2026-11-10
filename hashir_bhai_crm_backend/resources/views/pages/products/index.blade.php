@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Products</li>
@endsection

@php($money = fn ($value) => \App\Support\Money::format($value))

@section('page-actions')
    <a href="{{ url('/products/export') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-download me-1"></i> Export</a>
    @if (auth()->user()->can('products.create'))
        <a href="{{ url('/products/create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i> Add Product</a>
    @endif
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ number_format($stats['total']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-package me-1"></i>Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-success">{{ number_format($stats['active']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-check-circle me-1"></i>Active</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-info">{{ number_format($stats['services']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-badge-check me-1"></i>Services</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h4 class="mb-1 text-primary">{{ \App\Support\Money::compact($stats['stockValue']) }}</h4>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-layer me-1"></i>Stock Value</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card mb-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger">{{ number_format($stats['lowStock']) }}</h3>
                    <p class="text-muted mb-0 font-size-13"><i class="bx bx-error me-1"></i>Low Stock</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/products') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name or SKU">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        <option value="product" @selected(request('type') === 'product')>Product</option>
                        <option value="service" @selected(request('type') === 'service')>Service</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Stock</th>
                            <th>Visibility</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->imageUrl() }}" class="rounded" width="44" height="44" style="object-fit: cover;" alt="">
                                        <div class="ms-2">
                                            <a href="{{ url('/products/'.$product->id) }}" class="text-body fw-medium">{{ $product->name }}</a>
                                            <div class="text-muted font-size-12">
                                                {{ $product->sku ?? 'No SKU' }}@if ($product->brand) · {{ $product->brand }}@endif
                                                @if ($product->media_count > 0) · {{ $product->media_count }} media @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($product->type) }}</span></td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ $money($product->price) }}</span>
                                    @if ((float) $product->old_price > 0)
                                        <div class="text-muted font-size-12 text-decoration-line-through">{{ $money($product->old_price) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="{{ (int) $product->stock < 10 && $product->type === 'product' ? 'text-danger fw-semibold' : '' }}">{{ (int) $product->stock }}</span>
                                    <div class="text-muted font-size-12">{{ $product->unit ?? 'pcs' }}</div>
                                </td>
                                <td>
                                    @if ($product->is_featured)
                                        <span class="badge bg-warning-subtle text-warning">Featured</span>
                                    @endif
                                    @if ($product->show_to_bot)
                                        <span class="badge bg-success-subtle text-success"><i class="bx bx-bot"></i> Bot</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Hidden</span>
                                    @endif
                                    @if ($product->is_shareable_on_whatsapp)
                                        <span class="badge bg-info-subtle text-info"><i class="bx bxl-whatsapp"></i> WhatsApp</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($product->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    <a href="{{ url('/products/'.$product->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show"></i></a>
                                    @if (auth()->user()->can('products.update'))
                                        <a href="{{ url('/products/'.$product->id.'/edit') }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>
                                    @endif
                                    @if (auth()->user()->can('products.delete'))
                                        <form action="{{ url('/products/'.$product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $products->links() }}</div>
        </div>
    </div>
@endsection
