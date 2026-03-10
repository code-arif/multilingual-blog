<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    use HasFactory;

    const LOCALES = ['en', 'bn', 'es'];

    protected $fillable = [
        'post_id',
        'locale',
        'title',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        return substr(strip_tags($this->content ?? ''), 0, 200) . '...';
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}
