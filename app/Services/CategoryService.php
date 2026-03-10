<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getAll(bool $includeAll = false): Collection
    {
        $query = Category::withCount('posts');

        if (!$includeAll) {
            $query->active();
        }

        return $query->orderBy('name')->get();
    }

    public function getCategoryPosts(Category $category, string $locale, int $perPage = 10): LengthAwarePaginator
    {
        return $category->publishedPosts()
            ->with(['translations', 'author:id,name,avatar', 'category'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
