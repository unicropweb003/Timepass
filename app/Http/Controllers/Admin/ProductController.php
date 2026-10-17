<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mcp\Support\ImagePipeline;
use App\Models\Category;
use App\Models\Product;
use App\Services\CloudinaryUploader;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private CloudinaryUploader $uploader) {}

    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($request->query('category'), fn ($q, $cat) => $q->where('category', $cat))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(PerPage::from($request))->withQueryString();

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
        $data['image'] = $this->handleImage($request, null);

        try {
            Product::create($data);
        } catch (\Throwable $e) {
            $this->deleteImage($data['image']); // don't leave an orphan upload behind
            throw $e;
        }

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
        $old = $product->image;
        $data['image'] = $this->handleImage($request, $product);

        $product->update($data);

        // Remove the replaced image only once the new one is saved on the record.
        if ($data['image'] !== $old) {
            $this->deleteImage($old);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $image = $product->image;
        $product->delete();
        $this->deleteImage($image);

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
            'description' => ['nullable', 'string', 'max:500'],
            'details' => ['nullable', 'string', 'max:5000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', ...CloudinaryUploader::RULES],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'is_active' => ['nullable', 'boolean'],
        ], collect(CloudinaryUploader::MESSAGES)->mapWithKeys(fn ($msg, $rule) => ["image.$rule" => $msg])->all());

        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews'] = $validated['reviews'] ?? 0;
        $validated['tags'] = array_values($validated['tags'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['image']); // handled separately

        return $validated;
    }

    private function handleImage(Request $request, ?Product $product): ?string
    {
        if ($request->hasFile('image')) {
            // Uploaded to Cloudinary, not the server's disk: Render's disk is wiped on
            // every deploy/restart and is not shared between instances. The full
            // https secure_url is stored in the same `image` column as before.
            try {
                return $this->uploader->upload($request->file('image'), 'products');
            } catch (\Throwable $e) {
                Log::error('Product image upload failed', ['error' => CloudinaryUploader::safeMessage($e)]);

                throw ValidationException::withMessages(['image' => 'Could not upload the image. Please try again.']);
            }
        }

        return $product?->image;
    }

    /** Delete a stored image of any generation: Cloudinary URL, media row, or old local file. */
    private function deleteImage(?string $path): void
    {
        ImagePipeline::deleteStored($path); // Cloudinary file or media row

        // Older uploads were saved as files.
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
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
