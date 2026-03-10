<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List all users (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate((int) $request->get('limit', 15));

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Show a single user
     */
    public function show(int $id): JsonResponse
    {
        $user = User::with('posts')->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'User fetched successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update a user (admin only)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'bio' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Delete a user (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete your own account',
            ], 422);
        }

        // Reassign posts to the requesting admin
        $user->posts()->update(['author_id' => auth()->id()]);
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Update user role (admin only)
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_VISITOR])],
        ]);

        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot change your own role',
            ], 422);
        }

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'status' => true,
            'message' => 'User role updated successfully',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Get dashboard statistics (admin only)
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'draft_posts' => Post::where('status', Post::STATUS_DRAFT)->count(),
            'total_categories' => Category::count(),
            'total_users' => User::count(),
            'admin_users' => User::where('role', User::ROLE_ADMIN)->count(),
            'recent_posts' => Post::with(['author', 'translations', 'category'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($post) => [
                    'id' => $post->id,
                    'title' => optional($post->translations->firstWhere('locale', 'en'))->title ?? 'Untitled',
                    'status' => $post->status,
                    'author' => $post->author?->name,
                    'created_at' => $post->created_at->format('Y-m-d H:i'),
                ]),
            'recent_users' => User::latest()->limit(5)->get()->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->format('Y-m-d H:i'),
            ]),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Dashboard stats fetched',
            'data' => $stats,
        ]);
    }
}