<?php

namespace App\Modules\Review\Services;

use App\Modules\Review\Models\Review;

class ReviewService
{
    public function create(int $userId, int $productId, int $rating, ?string $comment = null): Review
    {
        return Review::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}
