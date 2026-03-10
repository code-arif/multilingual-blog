<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'color' => $this->color,
            'icon' => $this->icon,
            'posts_count' => $this->whenCounted('posts', fn() => $this->posts_count, 0),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
