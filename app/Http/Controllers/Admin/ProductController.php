<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mcp\Support\ImagePipeline;
use App\Models\Category;
use App\Models\Product;
use App\Services\CloudinaryUploader;
use App\Support\PerPage;
use App\Support\VideoUrl;
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
            $data['video'] = $this->handleVideo($request, null);
            Product::create($data);
        } catch (\Throwable $e) {
            // Don't leave orphan uploads behind.
            $this->deleteImage($data['image']);
            $this->uploader->delete($data['video'] ?? null);
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
        $oldVideo = $product->video;
        $data['image'] = $this->handleImage($request, $product);

        try {
            $data['video'] = $this->handleVideo($request, $product);
        } catch (\Throwable $e) {
            if ($data['image'] !== $old) {
                $this->deleteImage($data['image']); // the new image would be orphaned
            }
            throw $e;
        }

        $product->update($data);

        // Remove the replaced files only once the new ones are saved on the record.
        if ($data['image'] !== $old) {
            $this->deleteImage($old);
        }
        if ($data['video'] !== $oldVideo) {
            $this->uploader->delete($oldVideo); // ignores links that are not Cloudinary uploads
        }

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $image = $product->image;
        $video = $product->video;
        $product->delete();
        $this->deleteImage($image);
        $this->uploader->delete($video);

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
            'video' => ['nullable', ...CloudinaryUploader::VIDEO_RULES],
            'video_url' => ['nullable', 'string', 'max:2000', 'regex:'.VideoUrl::LINK_PATTERN],
            'remove_video' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'is_active' => ['nullable', 'boolean'],
        ], collect(CloudinaryUploader::MESSAGES)->mapWithKeys(fn ($msg, $rule) => ["image.$rule" => $msg])
            ->merge(collect(CloudinaryUploader::VIDEO_MESSAGES)->mapWithKeys(fn ($msg, $rule) => ["video.$rule" => $msg]))
            ->put('video_url.regex', VideoUrl::LINK_MESSAGE)
            ->all());

        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews'] = $validated['reviews'] ?? 0;
        $validated['tags'] = array_values($validated['tags'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['image'], $validated['video'], $validated['video_url'], $validated['remove_video']); // handled separately

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

    /**
     * The product's video: a new upload (to Cloudinary, like images), else a
     * pasted direct link, else removed when asked, else the current one.
     */
    private function handleVideo(Request $request, ?Product $product): ?string
    {
        if ($request->hasFile('video')) {
            try {
                return $this->uploader->uploadVideo($request->file('video'), 'products/videos');
            } catch (\Throwable $e) {
                Log::error('Product video upload failed', ['error' => CloudinaryUploader::safeMessage($e)]);

                throw ValidationException::withMessages(['video' => 'Could not upload the video. Please try again.']);
            }
        }

        if ($request->filled('video_url')) {
            return trim((string) $request->input('video_url'));
        }

        return $request->boolean('remove_video') ? null : $product?->video;
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
