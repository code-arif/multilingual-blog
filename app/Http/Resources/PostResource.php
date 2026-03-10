<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    private string $locale;

    public function __construct($resource, string $locale = null)
    {
        parent::__construct($resource);
        $this->locale = $locale ?? app()->getLocale();
    }

    public function toArray($request): array
    {
        $translation = $this->getTranslation($this->locale);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'featured_image_url' => $this->featured_image_url,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Translated fields
            'title' => $translation?->title ?? 'Untitled',
            'content' => $translation?->content,
            'excerpt' => $translation?->excerpt,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,

            // Available locales
            'available_locales' => $this->translations->pluck('locale')->toArray(),

            // All translations (for admin)
            'translations' => $this->when(
                request()->user()?->isAdmin(),
                fn() => $this->translations->keyBy('locale')->map(fn($t) => [
                    'title' => $t->title,
                    'content' => $t->content,
                    'excerpt' => $t->excerpt,
                    'meta_title' => $t->meta_title,
                    'meta_description' => $t->meta_description,
                ])
            ),

            // Relations
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'color' => $this->category->color,
                'icon' => $this->category->icon,
            ]),

            'author' => $this->whenLoaded('author', fn() => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar_url' => $this->author->avatar_url,
                'bio' => $this->when(
                    isset($this->resource->author->bio),
                    fn() => $this->author->bio
                ),
            ]),
        ];
    }
}
