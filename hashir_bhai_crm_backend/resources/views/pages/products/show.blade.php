@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/products') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@php
    $money = fn ($value) => \App\Support\Money::format($value);
    $mediaUrl = fn ($item) => str_starts_with((string) $item->path, 'http') ? $item->path : asset('storage/'.$item->path);
    $youtubeEmbed = function (?string $url): ?string {
        if (blank($url)) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|/embed/|v=)([A-Za-z0-9_-]{6,})~', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }
        return null;
    };
    $images = $product->media->where('type', 'image');
@endphp

@section('page-actions')
    @if (auth()->user()->can('products.update'))
        <a href="{{ url('/products/'.$product->id.'/edit') }}" class="btn btn-primary btn-sm"><i class="bx bx-edit me-1"></i> Edit</a>
    @endif
    @if (auth()->user()->can('products.delete'))
        <form action="{{ url('/products/'.$product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bx bx-trash me-1"></i> Delete</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <img src="{{ $product->imageUrl() }}" class="img-fluid rounded w-100 mb-3" style="max-height: 340px; object-fit: cover;" alt="{{ $product->name }}">
                    @if ($images->isNotEmpty())
                        <div class="row g-2">
                            @foreach ($images as $image)
                                <div class="col-3">
                                    <a href="{{ $mediaUrl($image) }}" target="_blank">
                                        <img src="{{ $mediaUrl($image) }}" class="rounded w-100" style="height: 64px; object-fit: cover;" alt="">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $product->name }}</h4>
                            <p class="text-muted mb-2">
                                {{ $product->sku ?? 'No SKU' }}
                                @if ($product->brand) · {{ $product->brand }}@endif
                                @if ($product->category) · <a href="{{ url('/products?category='.$product->category->id) }}" class="text-muted">{{ $product->category->name }}</a>@endif
                            </p>
                        </div>
                        <div>
                            @if (filled($product->badge))
                                <span class="badge bg-{{ $product->badge_class ?? 'primary' }}">{{ $product->badge }}</span>
                            @endif
                            @if ($product->is_featured)
                                <span class="badge bg-warning-subtle text-warning">Featured</span>
                            @endif
                            @if ($product->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-end mb-3">
                        <h3 class="text-primary mb-0 me-3">{{ $money($product->price) }}</h3>
                        @if ((float) $product->old_price > 0)
                            <span class="text-muted text-decoration-line-through me-3">{{ $money($product->old_price) }}</span>
                        @endif
                        <span class="badge bg-light text-dark">{{ ucfirst($product->type) }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="border rounded p-2 text-center">
                                <p class="text-muted mb-1 font-size-12">Stock</p>
                                <h5 class="mb-0 {{ (int) $product->stock < 10 && $product->type === 'product' ? 'text-danger' : '' }}">{{ (int) $product->stock }}</h5>
                                <span class="text-muted font-size-12">{{ $product->unit ?? 'pcs' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="border rounded p-2 text-center">
                                <p class="text-muted mb-1 font-size-12">Cost</p>
                                <h5 class="mb-0">{{ $money($product->cost) }}</h5>
                                <span class="text-muted font-size-12">Margin {{ \App\Support\Money::percent($product->margin()) }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="border rounded p-2 text-center">
                                <p class="text-muted mb-1 font-size-12">Tax Rate</p>
                                <h5 class="mb-0">{{ \App\Support\Money::percent($product->tax_rate) }}</h5>
                                <span class="text-muted font-size-12">Min order {{ (float) $product->min_order_qty }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="card mb-0 h-100"><div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($salesCount) }}</h4>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-cart me-1"></i>Sales Count</p>
                    </div></div>
                </div>
                <div class="col-sm-4">
                    <div class="card mb-0 h-100"><div class="card-body text-center">
                        <h4 class="mb-1 text-success">{{ \App\Support\Money::compact($salesTotal) }}</h4>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-dollar-circle me-1"></i>Sales Total</p>
                    </div></div>
                </div>
                <div class="col-sm-4">
                    <div class="card mb-0 h-100"><div class="card-body text-center">
                        <h4 class="mb-1">{{ $product->media->count() }}</h4>
                        <p class="text-muted mb-0 font-size-13"><i class="bx bx-images me-1"></i>Media Files</p>
                    </div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Description</h5></div>
                <div class="card-body">
                    @if (filled($product->description))
                        <p class="mb-3">{!! nl2br(e($product->description)) !!}</p>
                    @else
                        <p class="text-muted mb-3">No description provided.</p>
                    @endif
                    @if (filled($product->warranty))
                        <p class="mb-0"><span class="text-muted">Warranty:</span> {{ $product->warranty }}</p>
                    @endif
                    <p class="mb-0 mt-2">
                        <span class="badge bg-{{ $product->show_to_bot ? 'success' : 'secondary' }}-subtle text-{{ $product->show_to_bot ? 'success' : 'secondary' }}">
                            <i class="bx bx-bot me-1"></i>{{ $product->show_to_bot ? 'Visible to bot' : 'Hidden from bot' }}
                        </span>
                        <span class="badge bg-{{ $product->is_shareable_on_whatsapp ? 'info' : 'secondary' }}-subtle text-{{ $product->is_shareable_on_whatsapp ? 'info' : 'secondary' }}">
                            <i class="bx bxl-whatsapp me-1"></i>{{ $product->is_shareable_on_whatsapp ? 'WhatsApp shareable' : 'Not shareable' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-0 h-100">
                <div class="card-header"><h5 class="card-title mb-0">Specifications</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Key</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($product->specs ?? [] as $key => $value)
                                    <tr>
                                        <td class="text-muted">{{ $key }}</td>
                                        <td class="fw-medium">{{ $value }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">No specifications recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Bot Description</h5></div>
                <div class="card-body">
                    @if (filled($product->bot_description))
                        <p class="mb-0">{!! nl2br(e($product->bot_description)) !!}</p>
                    @else
                        <p class="text-muted mb-0">The bot falls back to the regular description for this product.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-0">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Media</h5>
                    @if (auth()->user()->can('products.update'))
                        <a href="{{ url('/products/'.$product->id.'/edit') }}" class="btn btn-sm btn-link">Manage media</a>
                    @endif
                </div>
                <div class="card-body">
                    @forelse ($product->media as $media)
                        <div class="d-flex align-items-center border-bottom py-2">
                            @if ($media->type === 'image')
                                <img src="{{ $mediaUrl($media) }}" class="rounded me-2" width="48" height="48" style="object-fit: cover;" alt="">
                            @else
                                <span class="cds-kpi-icon bg-light text-dark me-2"><i class="bx {{ $media->type === 'video' ? 'bx-video' : 'bx-file' }}"></i></span>
                            @endif
                            <div class="flex-1">
                                <h6 class="mb-0 font-size-14">{{ $media->title ?? 'Untitled' }}</h6>
                                <span class="text-muted font-size-12">{{ ucfirst($media->type) }}</span>
                            </div>
                            @if ($media->isExternal() && $youtubeEmbed($media->youtube_url))
                                <iframe width="160" height="90" src="{{ $youtubeEmbed($media->youtube_url) }}" class="rounded" frameborder="0" allowfullscreen></iframe>
                            @elseif ($media->type === 'video')
                                <video src="{{ $mediaUrl($media) }}" class="rounded" width="120" controls></video>
                            @else
                                <a href="{{ $mediaUrl($media) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bx bx-link-external"></i></a>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-muted py-4 mb-0">No media uploaded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
