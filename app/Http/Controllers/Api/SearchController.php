<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService)
    {
    }

    /**
     * Search posts across all languages
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $locale = $request->header('Accept-Language', config('app.locale'));
        app()->setLocale($locale);

        $results = $this->searchService->searchPosts(
            query: $request->get('q'),
            locale: $locale,
            perPage: (int) $request->get('limit', 10)
        );

        return response()->json([
            'status' => true,
            'message' => 'Search results fetched',
            'query' => $request->get('q'),
            'data' => PostResource::collection($results->items()),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'locale' => $locale,
            ],
        ]);
    }
}
