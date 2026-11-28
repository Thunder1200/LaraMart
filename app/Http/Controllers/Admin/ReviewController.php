<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Reviews\ModerateReviewAction;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('moderate', Review::class);

        $filter = (string) $request->query('durum', 'bekleyen');

        $query = Review::query()
            ->with(['user', 'product'])
            ->latest('id');

        if ($filter === 'bekleyen') {
            $query->where('is_approved', false);
        } elseif ($filter === 'onayli') {
            $query->where('is_approved', true);
        }

        return view('admin.reviews.index', [
            'reviews' => $query->paginate(20)->withQueryString(),
            'filter' => $filter,
            'pendingCount' => Review::query()->where('is_approved', false)->count(),
        ]);
    }

    public function approve(Review $review, ModerateReviewAction $action): RedirectResponse
    {
        Gate::authorize('moderate', $review);

        $action->approve($review);

        return back()->with('status', 'Yorum onaylandı ve ürün sayfasında yayınlandı.');
    }

    public function destroy(Review $review, ModerateReviewAction $action): RedirectResponse
    {
        Gate::authorize('moderate', $review);

        $action->reject($review);

        return back()->with('status', 'Yorum reddedildi ve kaldırıldı.');
    }
}
