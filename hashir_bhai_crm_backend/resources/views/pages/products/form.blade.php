@extends('layouts.app')

@php
    $isEdit = $product->exists;
    $specsText = old('specs_text');
    if ($specsText === null) {
        $specsText = collect($product->specs ?? [])->map(fn ($value, $key) => $key.': '.$value)->implode("\n");
    }
    $mediaUrl = fn ($item) => str_starts_with((string) $item->path, 'http') ? $item->path : asset('storage/'.$item->path);
@endphp

@section('title', $isEdit ? 'Edit Product' : 'Add Product')
@section('page-title', $isEdit ? 'Edit Product — '.$product->name : 'Add Product')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/products') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
@endsection

@section('page-actions')
    @if ($isEdit)
        <a href="{{ url('/products/'.$product->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-show me-1"></i> View Product</a>
    @endif
@endsection

@section('content')
    <form action="{{ $isEdit ? url('/products/'.$product->id) : url('/products') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Product Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $product->name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="product" @selected(old('type', $product->type) === 'product')>Product</option>
                                    <option value="service" @selected(old('type', $product->type) === 'service')>Service</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit) }}" placeholder="pcs">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Uncategorized</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" class="form-control" value="{{ old('color', $product->color) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" class="form-control" value="{{ old('tags', $product->tags) }}" placeholder="tag1, tag2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Warranty</label>
                                <input type="text" name="warranty" class="form-control" value="{{ old('warranty', $product->warranty) }}" placeholder="1 year">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Badge Label</label>
                                <input type="text" name="badge" class="form-control" value="{{ old('badge', $product->badge) }}" placeholder="New">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Badge Class</label>
                                <input type="text" name="badge_class" class="form-control" value="{{ old('badge_class', $product->badge_class) }}" placeholder="success">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" maxlength="5000">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bot Description</label>
                                <textarea name="bot_description" class="form-control" rows="4" maxlength="5000" placeholder="How the bot should describe this item to customers">{{ old('bot_description', $product->bot_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Specifications</h5>
                        <span class="text-muted font-size-12">One "Key: Value" per line</span>
                    </div>
                    <div class="card-body">
                        <textarea name="specs_text" class="form-control" rows="5" maxlength="3000" placeholder="Weight: 2kg&#10;Material: Aluminium">{{ $specsText }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Pricing</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $product->price) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Old Price</label>
                            <input type="number" step="0.01" min="0" name="old_price" class="form-control" value="{{ old('old_price', $product->old_price) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cost</label>
                            <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $product->cost) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="tax_rate" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Order Qty</label>
                            <input type="number" step="0.01" min="0" name="min_order_qty" class="form-control" value="{{ old('min_order_qty', $product->min_order_qty) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Stock</label>
                            <input type="number" step="1" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Image</h5></div>
                    <div class="card-body">
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if (filled($product->image))
                            <img src="{{ $product->imageUrl() }}" class="rounded mt-3" width="96" height="96" style="object-fit: cover;" alt="">
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Visibility</h5></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $product->is_featured))>
                            <label class="form-check-label" for="is_featured">Featured product</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active ?? true))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="show_to_bot" value="1" id="show_to_bot" @checked(old('show_to_bot', $product->show_to_bot ?? true))>
                            <label class="form-check-label" for="show_to_bot">Visible to bot</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_shareable_on_whatsapp" value="1" id="is_shareable_on_whatsapp" @checked(old('is_shareable_on_whatsapp', $product->is_shareable_on_whatsapp ?? true))>
                            <label class="form-check-label" for="is_shareable_on_whatsapp">Shareable on WhatsApp</label>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ url('/products') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Product' : 'Create Product' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($isEdit)
        <div class="row g-3 mt-1">
            <div class="col-lg-8">
                <div class="card mb-0">
                    <div class="card-header"><h5 class="card-title mb-0">Media Library</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Preview</th><th>Title</th><th>Type</th><th>Size</th><th class="text-end">Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($product->media as $media)
                                        <tr>
                                            <td>
                                                @if ($media->type === 'image')
                                                    <img src="{{ $mediaUrl($media) }}" class="rounded" width="44" height="44" style="object-fit: cover;" alt="">
                                                @else
                                                    <span class="cds-kpi-icon bg-light text-dark"><i class="bx {{ $media->type === 'video' ? 'bx-video' : 'bx-file' }}"></i></span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ $mediaUrl($media) }}" target="_blank" class="text-body fw-medium">{{ $media->title ?? 'Untitled' }}</a>
                                                @if ($media->isExternal())
                                                    <div class="text-muted font-size-12">{{ $media->youtube_url }}</div>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-light text-dark">{{ ucfirst($media->type) }}</span></td>
                                            <td class="text-muted font-size-13">{{ $media->size ? number_format($media->size / 1024, 1).' KB' : '—' }}</td>
                                            <td class="text-end cds-table-actions">
                                                <form action="{{ url('/products/media/'.$media->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this media file?');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No media uploaded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-0">
                    <div class="card-header"><h5 class="card-title mb-0">Add Media</h5></div>
                    <div class="card-body">
                        <form action="{{ url('/products/'.$product->id.'/media') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Files</label>
                                <input type="file" name="media[]" class="form-control" multiple>
                                <small class="text-muted">Images, videos or documents up to 50 MB each.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url') }}" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                            </div>
                            <button class="btn btn-primary w-100"><i class="bx bx-upload me-1"></i> Upload Media</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
