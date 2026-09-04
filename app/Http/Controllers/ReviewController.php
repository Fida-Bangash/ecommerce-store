<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Store a newly submitted product review from the storefront.
     *
     * New reviews always start out with a "pending" status and only
     * appear on the product page once an admin approves them from
     * the dashboard.
     */
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $product->reviews()->create([
            'user_id' => Auth::id(),
            'reviewer_name' => $data['reviewer_name'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('product.show', $product)
            ->with('success', 'Thank you! Your review has been submitted and is awaiting approval.');
    }

    /**
     * Display a paginated, filterable listing of all product reviews.
     *
     * Defaults to showing "pending" reviews when no status filter has
     * been explicitly chosen by the admin.
     */
    public function index(Request $request): View
    {
        $status = $request->has('status') ? $request->string('status')->toString() : 'pending';

        $reviews = Review::query()
            ->with('product')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('reviewer_name', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('reviews.index', [
            'reviews' => $reviews,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Return the full review details as JSON, used to populate the
     * "Review Details" modal.
     */
    public function show(Review $review): JsonResponse
    {
        $review->load('product');

        return response()->json([
            'review_id' => $review->id,
            'product_name' => $review->product->name,
            'reviewer_name' => $review->reviewer_name,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'status' => $review->status,
            'status_label' => $review->status_label,
            'date' => $review->created_at->format('d M Y, h:i A'),
        ]);
    }

    /**
     * Approve the specified review so it becomes visible on the
     * product page.
     */
    public function approve(Review $review): RedirectResponse
    {
        if (! $review->isApproved()) {
            $review->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review has been approved and is now visible on the product page.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
