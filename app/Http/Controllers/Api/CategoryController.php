<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService)
    {
    }

    /**
     * List all active categories
     */
    public function index(Request $request): JsonResponse
    {
        $includeAll = auth()->check() && auth()->user()->isAdmin();
        $categories = $this->categoryService->getAll($includeAll);

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Get posts for a specific category
     */
    public function posts(Request $request, string $slug): JsonResponse
    {
        $locale = $request->header('Accept-Language', config('app.locale'));
        app()->setLocale($locale);

        $category = Category::where('slug', $slug)->active()->firstOrFail();
        $posts = $this->categoryService->getCategoryPosts($category, $locale, (int) $request->get('limit', 10));

        return response()->json([
            'status' => true,
            'message' => 'Category posts fetched successfully',
            'data' => PostResource::collection($posts->items()),
            'meta' => [
                'category' => new CategoryResource($category),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
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
     * Create a new category (admin only)
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Update a category (admin only)
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category = $this->categoryService->update($category, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Delete a category (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->posts()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with existing posts',
            ], 422);
        }

        $this->categoryService->delete($category);

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}