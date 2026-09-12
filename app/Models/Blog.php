<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'image',
        'tags', 'published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * Strip HTML tags from excerpt for display.
     */
    public function getExcerptAttribute(?string $value): ?string
    {
        return $value ? trim(strip_tags($value)) : null;
    }

}
