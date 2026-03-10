<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    /**
     * Get paginated posts with translations
     */
    public function getPaginatedPosts(
        string $locale = 'en',
        ?string $categorySlug = null,
        ?string $search = null,
        int $perPage = 10,
        bool $includeAll = false
    ): LengthAwarePaginator {
        $query = Post::with([
            'translations',
            'category',
            'author:id,name,avatar',
        ]);

        if (!$includeAll) {
            $query->published();
        }

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        if ($search) {
            $query->whereHas('translations', function ($q) use ($search, $locale) {
                $q->where('locale', $locale)
                  ->where(function ($sq) use ($search) {
                      $sq->where('title', 'like', "%{$search}%")
                         ->orWhere('content', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest('published_at')->paginate($perPage);
    }

    /**
     * Get a post by slug
     */
    public function getPostBySlug(string $slug, string $locale = 'en'): ?Post
    {
        return Post::with([
            'translations',
            'category',
            'author:id,name,avatar,bio',
        ])->where('slug', $slug)->first();
    }

    /**
     * Create a new post with translations
     */
    public function createPost(array $data, int $authorId): Post
    {
        return DB::transaction(function () use ($data, $authorId) {
            $post = Post::create([
                'category_id' => $data['category_id'],
                'author_id' => $authorId,
                'featured_image' => $data['featured_image'] ?? null,
                'status' => $data['status'] ?? Post::STATUS_DRAFT,
                'published_at' => ($data['status'] ?? '') === Post::STATUS_PUBLISHED ? now() : null,
                'slug' => '', // Will be generated
            ]);

            // Create translations
            foreach ($data['translations'] as $locale => $translation) {
                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => $locale,
                    'title' => $translation['title'],
                    'content' => $translation['content'],
                    'excerpt' => $translation['excerpt'] ?? null,
                    'meta_title' => $translation['meta_title'] ?? $translation['title'],
                    'meta_description' => $translation['meta_description'] ?? null,
                ]);
            }

            // Now generate slug from English translation
            $post->load('translations');
            $post->generateSlug();
            $post->save();

            return $post->load(['translations', 'category', 'author']);
        });
    }

    /**
     * Update an existing post and its translations
     */
    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $updateData = array_filter([
                'category_id' => $data['category_id'] ?? null,
                'featured_image' => $data['featured_image'] ?? null,
                'status' => $data['status'] ?? null,
            ], fn($v) => $v !== null);

            if (isset($data['status']) && $data['status'] === Post::STATUS_PUBLISHED && !$post->published_at) {
                $updateData['published_at'] = now();
            }

            $post->update($updateData);

            // Update translations
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $locale => $translation) {
                    PostTranslation::updateOrCreate(
                        ['post_id' => $post->id, 'locale' => $locale],
                        [
                            'title' => $translation['title'],
                            'content' => $translation['content'],
                            'excerpt' => $translation['excerpt'] ?? null,
                            'meta_title' => $translation['meta_title'] ?? $translation['title'],
                            'meta_description' => $translation['meta_description'] ?? null,
                        ]
                    );
                }
            }

            // Invalidate cache
            Cache::forget("post_{$post->slug}");
            Cache::tags(['posts'])->flush();

            return $post->load(['translations', 'category', 'author']);
        });
    }

    /**
     * Delete a post (soft delete)
     */
    public function deletePost(Post $post): void
    {
        // Delete associated image if exists
        if ($post->featured_image) {
            Storage::disk('public')->delete('uploads/posts/' . $post->featured_image);
        }

        Cache::forget("post_{$post->slug}");
        $post->delete();
    }

    /**
     * Publish a post
     */
    public function publishPost(Post $post): void
    {
        $post->update([
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => $post->published_at ?? now(),
        ]);
        Cache::tags(['posts'])->flush();
    }

    /**
     * Unpublish a post
     */
    public function unpublishPost(Post $post): void
    {
        $post->update(['status' => Post::STATUS_DRAFT]);
        Cache::forget("post_{$post->slug}");
    }
}
