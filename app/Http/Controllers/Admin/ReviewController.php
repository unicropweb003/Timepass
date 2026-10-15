<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::orderBy('sort_order')->orderByDesc('id')->paginate(12);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function create()
    {
        return view('admin.reviews.form', [
            'review' => new Review(['rating' => 5, 'is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        Review::create($this->validateData($request));

        return redirect()->route('admin.reviews.index')->with('status', 'Review added.');
    }

    public function edit(Review $review)
    {
        return view('admin.reviews.form', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $review->update($this->validateData($request));

        return redirect()->route('admin.reviews.index')->with('status', 'Review updated.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['required', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
