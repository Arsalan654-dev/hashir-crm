<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category:id,name'])
            ->withCount('media')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('name', 'like', $search)->orWhere('sku', 'like', $search));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'stats' => [
                'total' => Product::query()->count(),
                'active' => Product::query()->where('is_active', true)->count(),
                'services' => Product::query()->where('type', 'service')->count(),
                'stockValue' => (float) Product::query()->sum(DB::raw('stock * price')),
                'lowStock' => Product::query()->where('type', 'product')->where('stock', '<', 10)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.products.form', [
            'product' => new Product(['type' => 'product', 'unit' => 'pcs', 'is_active' => true, 'show_to_bot' => true, 'is_shareable_on_whatsapp' => true]),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['show_to_bot'] = $request->boolean('show_to_bot', true);
        $data['is_shareable_on_whatsapp'] = $request->boolean('is_shareable_on_whatsapp', true);
        $data['specs'] = $this->parseSpecs($request->input('specs_text'));

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::query()->create($data);

        $this->storeMediaFiles($request, $product);

        Activity::log('created', 'Product created: '.$product->name, $product, [], 'products');

        return redirect()->route('products.show', $product)->with('status', 'Product created.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'media' => fn ($query) => $query->orderBy('sort_order')]);

        return view('pages.products.show', [
            'product' => $product,
            'salesCount' => $product->saleItems()->count(),
            'salesTotal' => (float) $product->saleItems()->sum('total') ?? 0.0,
        ]);
    }

    public function edit(Product $product): View
    {
        return view('pages.products.form', [
            'product' => $product->load('media'),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);

        $data['is_active'] = $request->boolean('is_active');
        $data['show_to_bot'] = $request->boolean('show_to_bot');
        $data['is_shareable_on_whatsapp'] = $request->boolean('is_shareable_on_whatsapp');
        $data['specs'] = $this->parseSpecs($request->input('specs_text'));

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        $this->storeMediaFiles($request, $product);

        Activity::log('updated', 'Product updated: '.$product->name, $product, [], 'products');

        return redirect()->route('products.show', $product)->with('status', 'Product updated.');
    }

    public function storeMedia(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'media' => ['required', 'array'],
            'media.*' => ['file', 'max:51200'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->filled('youtube_url')) {
            ProductMedia::query()->create([
                'product_id' => $product->id,
                'type' => 'video',
                'path' => $request->string('youtube_url'),
                'youtube_url' => $request->string('youtube_url'),
                'title' => $request->input('title'),
            ]);
        }

        $this->storeMediaFiles($request, $product);

        return back()->with('status', 'Media uploaded.');
    }

    public function destroyMedia(ProductMedia $media): RedirectResponse
    {
        $media->delete();

        return back()->with('status', 'Media removed.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Activity::log('deleted', 'Product deleted: '.$product->name, null, ['product_id' => $product->id], 'products');
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'SKU', 'Type', 'Category', 'Price', 'Cost', 'Stock', 'Unit', 'Active', 'Bot Visible']);

            Product::query()->with('category:id,name')->chunk(500, function (Collection $products) use ($handle) {
                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->name, $product->sku, $product->type, $product->category?->name,
                        $product->price, $product->cost, $product->stock, $product->unit,
                        $product->is_active ? 'yes' : 'no', $product->show_to_bot ? 'yes' : 'no',
                    ]);
                }
            });

            fclose($handle);
        }, 'products-export-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:product,service'],
            'sku' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:150'],
            'unit' => ['nullable', 'string', 'max:20'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'color' => ['nullable', 'string', 'max:60'],
            'tags' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_order_qty' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:50'],
            'badge_class' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'bot_description' => ['nullable', 'string', 'max:5000'],
            'warranty' => ['nullable', 'string', 'max:150'],
            'specs_text' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'show_to_bot' => ['nullable', 'boolean'],
            'is_shareable_on_whatsapp' => ['nullable', 'boolean'],
        ]);

        unset($data['specs_text'], $data['image']);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['stock'] = $data['stock'] ?? 0;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function parseSpecs(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        $specs = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $text) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $key = trim($key);

            if ($key !== '') {
                $specs[$key] = trim($value);
            }
        }

        return $specs;
    }

    private function storeMediaFiles(Request $request, Product $product): void
    {
        if (! $request->hasFile('media')) {
            return;
        }

        foreach ($request->file('media') as $index => $file) {
            $mime = (string) $file->getClientMimeType();
            $type = match (true) {
                Str::startsWith($mime, 'image/') => 'image',
                Str::startsWith($mime, 'video/') => 'video',
                default => 'document',
            };

            ProductMedia::query()->create([
                'product_id' => $product->id,
                'type' => $type,
                'path' => $file->store('products/media', 'public'),
                'title' => $product->name,
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'sort_order' => $index,
            ]);
        }
    }
}
