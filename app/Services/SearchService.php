<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    public function searchPosts(string $query, string $locale = 'en', int $perPage = 10): LengthAwarePaginator
    {
        return Post::with(['translations', 'category', 'author:id,name,avatar'])
            ->published()
            ->whereHas('translations', function ($q) use ($query, $locale) {
                $q->where('locale', $locale)
                  ->where(function ($sq) use ($query) {
                      $sq->where('title', 'like', "%{$query}%")
                         ->orWhere('content', 'like', "%{$query}%")
                         ->orWhere('excerpt', 'like', "%{$query}%");
                  });
            })
            ->orderByRaw(
                "CASE WHEN (
                    SELECT title FROM post_translations
                    WHERE post_id = posts.id AND locale = ?
                    LIMIT 1
                ) LIKE ? THEN 1 ELSE 2 END",
                [$locale, "%{$query}%"]
            )
            ->paginate($perPage);
    }
}
