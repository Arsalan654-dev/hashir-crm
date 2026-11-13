@extends('layouts.app')

@section('title', 'Knowledge Base')
@section('page-title', 'Knowledge Base')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/bot/conversations') }}">AI Bot</a></li>
    <li class="breadcrumb-item active">Knowledge Base</li>
@endsection

@section('page-actions')
    @if (auth()->user()->can('knowledge.manage'))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addArticleModal">
            <i class="bx bx-plus me-1"></i> Add Article
        </button>
    @endif
    @if (auth()->user()->can('bot.settings'))
        <a href="{{ url('/bot/settings') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-cog me-1"></i> Bot Settings</a>
    @endif
@endsection

@section('content')
    <div class="alert alert-info d-flex align-items-start">
        <i class="bx bx-bot font-size-20 me-2"></i>
        <div>
            These articles — together with your product media — feed the WhatsApp AI bot, so keep them accurate.
            The bot fetches them from <code>{{ url('/api/bot/knowledge') }}</code>. Only <strong>Active</strong> articles with <strong>Share with bot</strong> enabled are exposed.
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/knowledge') }}">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title or content">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Filter</button>
                </div>
                @if (request()->filled('search') || request()->filled('category'))
                    <div class="col-md-2">
                        <a href="{{ url('/knowledge') }}" class="btn btn-light w-100">Clear</a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Article</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Bot</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td>
                                    <h6 class="mb-0 font-size-14">{{ $article->title }}</h6>
                                    <span class="text-muted font-size-12">
                                        {{ $article->product?->name ?? 'General' }} &middot; {{ $article->creator?->name ?? 'System' }}
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $article->type)) }}</span></td>
                                <td>{{ $article->category }}</td>
                                <td>{{ $article->priority }}</td>
                                <td>
                                    @if ($article->share_with_bot)
                                        <span class="badge bg-success-subtle text-success"><i class="bx bx-bot me-1"></i>Shared</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Internal</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($article->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    @if (auth()->user()->can('knowledge.manage'))
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editArticle{{ $article->id }}"><i class="bx bx-edit"></i></button>
                                        <form action="{{ url('/knowledge/'.$article->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this article?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No knowledge articles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $articles->links() }}</div>
        </div>
    </div>

    @if (auth()->user()->can('knowledge.manage'))
        <div class="modal fade" id="addArticleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ url('/knowledge') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Knowledge Article</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <input type="text" name="category" class="form-control" value="{{ old('category', 'general') }}" list="knowledgeCategories" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach (\App\Models\KnowledgeArticle::TYPES as $type)
                                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Priority</label>
                                    <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0" max="9999">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">No product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Content <span class="text-danger">*</span></label>
                                    <textarea name="content" class="form-control" rows="6" required>{{ old('content') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tags</label>
                                    <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="pricing, delivery, warranty">
                                    <small class="text-muted">Comma separated.</small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add-article-active" @checked(old('is_active', true))>
                                        <label class="form-check-label" for="add-article-active">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="share_with_bot" value="1" id="add-article-bot" @checked(old('share_with_bot', true))>
                                        <label class="form-check-label" for="add-article-bot">Share with bot</label>
                                    </div>
                                </div>
                            </div>
                            <datalist id="knowledgeCategories">
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Create Article</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach ($articles as $article)
            <div class="modal fade" id="editArticle{{ $article->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <form action="{{ url('/knowledge/'.$article->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Article — {{ $article->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="{{ $article->title }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <input type="text" name="category" class="form-control" value="{{ $article->category }}" list="knowledgeCategories" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach (\App\Models\KnowledgeArticle::TYPES as $type)
                                                <option value="{{ $type }}" @selected($article->type === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control" value="{{ $article->priority }}" min="0" max="9999">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Product</label>
                                        <select name="product_id" class="form-select">
                                            <option value="">No product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" @selected($article->product_id == $product->id)>{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea name="content" class="form-control" rows="6" required>{{ $article->content }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tags</label>
                                        <input type="text" name="tags" class="form-control" value="{{ is_array($article->tags) ? implode(', ', $article->tags) : $article->tags }}">
                                        <small class="text-muted">Comma separated.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-article-active-{{ $article->id }}" @checked($article->is_active)>
                                            <label class="form-check-label" for="edit-article-active-{{ $article->id }}">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="share_with_bot" value="1" id="edit-article-bot-{{ $article->id }}" @checked($article->share_with_bot)>
                                            <label class="form-check-label" for="edit-article-bot-{{ $article->id }}">Share with bot</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Article</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
