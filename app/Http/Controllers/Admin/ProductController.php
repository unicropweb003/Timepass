<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($request->query('category'), fn ($q, $cat) => $q->where('category', $cat))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(12)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'rating' => 0, 'reviews' => 0]),
            'categories' => Category::orderBy('name')->get(),
            'forms' => config('shop.forms', []),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image'] = $this->handleImage($request, null, $data['slug']);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'forms' => config('shop.forms', []),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request);
        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }
        $data['image'] = $this->handleImage($request, $product, $data['slug'] ?? $product->slug);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->deleteImage($product->image);
        $product->delete();

        return back()->with('status', 'Product deleted.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'exists:categories,slug'],
            'type' => ['nullable', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'mrp' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews'] = $validated['reviews'] ?? 0;
        $validated['tags'] = array_values($validated['tags'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['image']); // handled separately

        return $validated;
    }

    private function handleImage(Request $request, ?Product $product, string $slug): ?string
    {
        if ($request->hasFile('image')) {
            if ($product) {
                $this->deleteImage($product->image);
            }
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
            // Name the file after the product (its slug) instead of a random string,
            // so uploads are human-readable; a short time suffix keeps it unique and
            // avoids the browser showing a cached old image after a re-upload.
            $name = $this->uniqueImageName($slug, $ext);
            $file->move(public_path('images/products'), $name);
            return 'images/products/' . $name;
        }

        return $product?->image;
    }

    private function uniqueImageName(string $slug, string $ext): string
    {
        $base = Str::slug($slug) ?: 'product';
        $name = $base . '.' . $ext;
        // Collision guard: only add a suffix if a file with that name already exists.
        if (file_exists(public_path('images/products/' . $name))) {
            $name = $base . '-' . now()->format('YmdHis') . '.' . $ext;
        }
        return $name;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'images/products/') && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
