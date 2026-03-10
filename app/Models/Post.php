<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'category_id',
        'author_id',
        'slug',
        'featured_image',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                // Use the English translation title for slug generation
                $translation = $model->translations()->where('locale', 'en')->first();
                return $translation ? $translation->title : 'post-' . time();
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(100);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->hasOne(PostTranslation::class)->where('locale', $locale);
    }

    // Get translation with fallback to English
    public function getTranslation(string $locale = null): ?PostTranslation
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale);

        if (!$translation) {
            $translation = $this->translations->firstWhere('locale', 'en');
        }

        return $translation;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });
    }

    // Accessors
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->featured_image) {
            return asset('storage/uploads/posts/' . $this->featured_image);
        }
        return null;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
