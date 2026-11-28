<?php

namespace App\Actions\Reviews;

use App\Models\Review;
use App\Support\CatalogCache;

class ModerateReviewAction
{
    public function approve(Review $review): Review
    {
        $review->update(['is_approved' => true]);
        CatalogCache::flush();

        return $review->refresh();
    }

    public function reject(Review $review): void
    {
        $review->delete();
        CatalogCache::flush();
    }
}
