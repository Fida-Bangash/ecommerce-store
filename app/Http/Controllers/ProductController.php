<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * The storage disk used for product images.
     */
    private const IMAGE_DISK = 'public';

    /**
     * The storage directory used for product images.
     */
    private const IMAGE_PATH = 'products';

    /**
     * Display a paginated listing of the products.
     */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category'));
            })
            ->when($request->filled('stock'), function ($query) use ($request) {
                $query->stockStatus($request->string('stock'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::query()->active()->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['variants']);

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            $this->storeImages($product, $request->file('images'));
        }

        $this->syncVariants($product, $variants);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load('images', 'variants');
        $categories = Category::query()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Display the given product's public detail page.
     */
    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load(['images', 'category', 'variants', 'approvedReviews']);

        $relatedProducts = Product::query()
            ->with(['images', 'category', 'variants'])
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name'], $product->id);
        $removeImages = $data['remove_images'] ?? [];
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['remove_images'], $data['variants']);

        $product->update($data);

        if (! empty($removeImages)) {
            $this->removeImages($product, $removeImages);
        }

        if ($request->hasFile('images')) {
            $this->storeImages($product, $request->file('images'));
        }

        $this->syncVariants($product, $variants);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Add stock quantity to the specified product, keeping a history
     * record of when the stock was added and how much was added.
     */
    public function addStock(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $stockBefore = $product->stock_quantity;

        $product->increment('stock_quantity', $data['quantity']);

        $product->stockAdditions()->create([
            'user_id' => $request->user()?->id,
            'quantity' => $data['quantity'],
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock_quantity,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', "Stock updated for \"{$product->name}\". Added {$data['quantity']} unit(s) on ".now()->format('d M Y, h:i A').", new total: {$product->stock_quantity}.");
    }

    /**
     * Get the stock addition history for the specified product as JSON,
     * used to populate the "Stock History" modal on the product list.
     */
    public function stockHistory(Product $product): \Illuminate\Http\JsonResponse
    {
        $history = $product->stockAdditions()
            ->with('user:id,name')
            ->get()
            ->map(fn (\App\Models\StockAddition $addition) => [
                'date' => $addition->created_at->format('d M Y, h:i A'),
                'quantity' => $addition->quantity,
                'stock_before' => $addition->stock_before,
                'stock_after' => $addition->stock_after,
                'added_by' => $addition->user?->name,
            ]);

        return response()->json([
            'product' => $product->name,
            'current_stock' => $product->stock_quantity,
            'history' => $history,
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->load('images');

        foreach ($product->images as $image) {
            $this->deleteImage($image->path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Resolve a unique slug for the product, generating one from the
     * name when a custom slug is not provided.
     */
    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $uniqueSlug = $baseSlug;
        $suffix = 1;

        while (
            Product::query()
                ->where('slug', $uniqueSlug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $uniqueSlug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $uniqueSlug;
    }

    /**
     * Store the uploaded product images and attach them to the product.
     *
     * @param  array<int, UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        $sortOrder = $product->images()->max('sort_order') + 1;

        foreach ($images as $image) {
            $product->images()->create([
                'path' => $image->store(self::IMAGE_PATH, self::IMAGE_DISK),
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    /**
     * Delete the given product images by id and remove their files.
     *
     * @param  array<int, int|string>  $imageIds
     */
    private function removeImages(Product $product, array $imageIds): void
    {
        $images = ProductImage::query()
            ->where('product_id', $product->id)
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            $this->deleteImage($image->path);
            $image->delete();
        }
    }

    /**
     * Delete a product image file from storage, if it exists.
     */
    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk(self::IMAGE_DISK)->exists($path)) {
            Storage::disk(self::IMAGE_DISK)->delete($path);
        }
    }

    /**
     * Sync the product's size/color variants with the submitted rows.
     *
     * Rows with an "id" are updated, rows without one are created,
     * and any existing variant not present in the submitted rows is
     * deleted. Rows that are completely empty (no size, no color)
     * are ignored.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $size = trim((string) ($row['size'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));

            if ($size === '' && $color === '') {
                continue;
            }

            $attributes = [
                'size' => $size !== '' ? $size : null,
                'color' => $color !== '' ? $color : null,
                'color_hex' => ($row['color_hex'] ?? '') !== '' ? $row['color_hex'] : null,
                'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                'extra_price' => ($row['extra_price'] ?? '') !== ''
                    ? (float) $row['extra_price']
                    : 0,
            ];

            if (! empty($row['id'])) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->find($row['id']);

                if ($variant) {
                    $variant->update($attributes);
                    $keptIds[] = $variant->id;

                    continue;
                }
            }

            $variant = $product->variants()->create($attributes);
            $keptIds[] = $variant->id;
        }

        $product->variants()
            ->whereNotIn('id', $keptIds)
            ->delete();
    }
}
