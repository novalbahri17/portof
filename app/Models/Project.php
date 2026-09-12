<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image', 'url', 'repo_url',
        'tags', 'challenge', 'solution', 'results', 'features', 'gallery',
        'technologies', 'type', 'featured', 'sort_order', 'published',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'features' => 'array',
            'gallery' => 'array',
            'technologies' => 'array',
            'featured' => 'boolean',
            'published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeSideProjects($query)
    {
        return $query->where('type', 'side_project');
    }

    public function scopePortfolio($query)
    {
        return $query->where('type', 'portfolio');
    }
}
