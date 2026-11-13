<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\Product;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $articles = KnowledgeArticle::query()
            ->with(['product:id,name', 'creator:id,name'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('title', 'like', $search)->orWhere('content', 'like', $search));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->orderByDesc('priority')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('pages.knowledge.index', [
            'articles' => $articles,
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'categories' => KnowledgeArticle::query()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateArticle($request);

        $article = KnowledgeArticle::query()->create($data + [
            'created_by' => $request->user()->id,
            'share_with_bot' => $request->boolean('share_with_bot', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Activity::log('created', 'Knowledge article created: '.$article->title, $article, [], 'knowledge');

        return back()->with('status', 'Knowledge article created.');
    }

    public function update(Request $request, KnowledgeArticle $article): RedirectResponse
    {
        $data = $this->validateArticle($request);

        $article->update($data + [
            'share_with_bot' => $request->boolean('share_with_bot'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Knowledge article updated.');
    }

    public function destroy(KnowledgeArticle $article): RedirectResponse
    {
        $article->delete();

        return back()->with('status', 'Knowledge article deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:'.implode(',', KnowledgeArticle::TYPES)],
            'content' => ['required', 'string', 'max:20000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_id' => ['nullable', 'exists:products,id'],
            'is_active' => ['nullable', 'boolean'],
            'share_with_bot' => ['nullable', 'boolean'],
        ]);
    }
}
