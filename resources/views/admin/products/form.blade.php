@extends('layouts.admin', ['title' => $product->exists ? 'Edit product' : 'New product'])

@section('content')
@php
    $typesByCategory = $categories->mapWithKeys(fn ($c) => [$c->slug => $c->types ?: []]);
    $selectedTags = old('tags', $product->tags ?? []);
@endphp

<a href="{{ route('admin.products.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to products
</a>

<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="max-w-3xl">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Product name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" id="category-select" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">Select…</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(old('category', $product->category) === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type-select"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">—</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Form</label>
                <select name="form"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">—</option>
                    @foreach ($forms as $f)
                        <option value="{{ $f['slug'] }}" @selected(old('form', $product->form) === $f['slug'])>{{ $f['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Price (₹)</label>
                <input type="number" name="price" min="0" value="{{ old('price', $product->price) }}" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">MRP (₹) <span class="text-gray-400">— optional</span></label>
                <input type="number" name="mrp" min="0" value="{{ old('mrp', $product->mrp) }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Short description <span class="text-gray-400">— shown on product cards</span></label>
            <textarea name="description" rows="2" maxlength="500"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">{{ old('description', $product->description) }}</textarea>
            <p class="mt-1 text-xs text-gray-400">A one or two line summary (max 500 characters).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Full description <span class="text-gray-400">— shown on the product page</span></label>
            <textarea name="details" rows="8"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">{{ old('details', $product->details) }}</textarea>
            <p class="mt-1 text-xs text-gray-400">The detailed description shown under "The good stuff inside". Write multiple paragraphs; start a line with "• " for bullet points.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Rating (0–5)</label>
                <input type="number" name="rating" step="0.1" min="0" max="5" value="{{ old('rating', $product->rating) }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Reviews count</label>
                <input type="number" name="reviews" min="0" value="{{ old('reviews', $product->reviews) }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Product image</label>
            @if ($product->image)
                <img src="{{ $product->image_url }}" alt="" class="mt-2 h-20 w-20 rounded-lg object-cover">
            @endif
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
            <p class="mt-1 text-xs text-gray-400">JPG, JPEG, PNG or WebP, up to 5 MB. Leave empty to keep the current image.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Product video <span class="text-gray-400">— optional, shown in "Watch &amp; shop" on the home page</span></label>
            @if ($product->video)
                <div class="mt-2 flex items-start gap-4">
                    <video src="{{ \App\Support\VideoUrl::sources($product->video)[0] }}" poster="{{ \App\Support\VideoUrl::poster($product->video) }}"
                        class="h-40 w-[5.625rem] rounded-lg bg-gray-900 object-cover" muted loop playsinline controls preload="metadata"></video>
                    <label class="mt-1 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="remove_video" value="1" @checked(old('remove_video'))
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        Remove this video
                    </label>
                </div>
            @endif
            <input type="file" name="video" id="video-input" accept=".mp4,.mov,.webm,.m4v,video/mp4,video/quicktime,video/webm"
                class="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
            <p id="video-size-error" class="mt-1 hidden text-xs font-medium text-red-600">This video is larger than 40 MB. Please use a shorter clip.</p>
            <p class="mt-1 text-xs text-gray-400">A short upright clip (5–20 seconds, 9:16, like a phone video) of a model showing the product. MP4, MOV or WebM, up to 40 MB. Plays silently on the home page; shoppers can tap it to watch with sound.</p>
            <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="…or paste a direct link to a video file (https://…/clip.mp4)"
                class="mt-3 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tags</label>
            <div class="mt-2 flex flex-wrap gap-4">
                @foreach (['best-seller' => 'Best seller', 'recommended' => 'Recommended'] as $tag => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="tags[]" value="{{ $tag }}" @checked(in_array($tag, $selectedTags))
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Active (visible on the store)
        </label>
    </div>

    <div class="mt-5 flex gap-3">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-500">
            {{ $product->exists ? 'Save changes' : 'Create product' }}
        </button>
        <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>

<script>
    const typesByCategory = @json($typesByCategory);
    const currentType = @json(old('type', $product->type));
    const categorySelect = document.getElementById('category-select');
    const typeSelect = document.getElementById('type-select');

    function populateTypes() {
        const types = typesByCategory[categorySelect.value] || {};
        typeSelect.innerHTML = '<option value="">—</option>';
        for (const [slug, name] of Object.entries(types)) {
            const opt = document.createElement('option');
            opt.value = slug;
            opt.textContent = name;
            if (slug === currentType) opt.selected = true;
            typeSelect.appendChild(opt);
        }
    }
    categorySelect.addEventListener('change', populateTypes);
    populateTypes();

    // Stop an over-size video before it is sent: the server would drop the whole form.
    const videoInput = document.getElementById('video-input');
    const videoError = document.getElementById('video-size-error');
    const tooBig = () => (videoInput.files[0]?.size || 0) > 40 * 1024 * 1024;
    videoInput.addEventListener('change', () => videoError.classList.toggle('hidden', !tooBig()));
    videoInput.form.addEventListener('submit', (e) => {
        if (tooBig()) { e.preventDefault(); videoError.classList.remove('hidden'); videoInput.focus(); }
    });
</script>
@endsection
