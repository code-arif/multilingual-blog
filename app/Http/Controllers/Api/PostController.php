<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use App\Services\PostService;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private PostService $postService)
    {
    }

    /**
     * List all published posts with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->header('Accept-Language', config('app.locale'));
        app()->setLocale($locale);

        $posts = $this->postService->getPaginatedPosts(
            locale: $locale,
            categorySlug: $request->get('category'),
            search: $request->get('search'),
            perPage: (int) $request->get('limit', 10),
            includeAll: auth()->check() && auth()->user()->isAdmin(),
        );

        return response()->json([
            'status' => true,
            'message' => 'Posts fetched successfully',
            'data' => PostResource::collection($posts->items()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'locale' => $locale,
            ],
            'links' => [
                'first' => $posts->url(1),
                'last' => $posts->url($posts->lastPage()),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show a single post by slug
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $request->header('Accept-Language', config('app.locale'));
        app()->setLocale($locale);

        $post = $this->postService->getPostBySlug($slug, $locale);

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Only admins can view draft posts
        if (!$post->isPublished() && !(auth()->check() && auth()->user()->isAdmin())) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Post fetched successfully',
            'data' => new PostResource($post, $locale),
        ]);
    }

    /**
     * Create a new post (admin only)
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->createPost(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Update an existing post (admin only)
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $post = $this->postService->updatePost($post, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Delete a post (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $this->postService->deletePost($post);

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Publish a post (admin only)
     */
    public function publish(int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $this->postService->publishPost($post);

        return response()->json([
            'status' => true,
            'message' => 'Post published successfully',
            'data' => new PostResource($post->fresh()),
        ]);
    }

    /**
     * Unpublish a post (admin only)
     */
    public function unpublish(int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $this->postService->unpublishPost($post);

        return response()->json([
            'status' => true,
            'message' => 'Post unpublished successfully',
            'data' => new PostResource($post->fresh()),
        ]);
    }
}
