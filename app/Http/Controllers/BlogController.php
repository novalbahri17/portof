<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\SiteSetting;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function show(string $slug)
    {
        $blog = Blog::published()->where('slug', $slug)->firstOrFail();

        // Get related blogs (same tags, exclude current)
        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog) {
                if ($blog->tags) {
                    foreach ($blog->tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                }
            })
            ->latest('published_at')
            ->limit(3)
            ->get();

        // SEO data with fallbacks
        $blogUrl = url("/blog/{$blog->slug}");

        // Clean description: strip HTML, limit to 150 chars
        $rawDescription = $blog->excerpt ?: strip_tags($blog->content);
        $seoDescription = Str::limit(trim(strip_tags($rawDescription)), 150);

        // Title: just the blog title, max 60 chars
        $seoTitle = Str::limit($blog->title, 60);

        return Inertia::render('Blog', [
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'keywords' => $blog->tags ? implode(', ', $blog->tags) : '',
                'canonical' => $blogUrl,
                'ogTitle' => $seoTitle,
                'ogDescription' => $seoDescription,
                'ogImage' => $blog->image ? asset("storage/{$blog->image}") : '',
                'ogType' => 'article',
                'twitterCard' => 'summary_large_image',
                'twitterTitle' => $seoTitle,
                'twitterDescription' => $seoDescription,
                'twitterImage' => $blog->image ? asset("storage/{$blog->image}") : '',
                'favicon' => SiteSetting::get('favicon', ''),
                'publishedAt' => $blog->published_at?->toIso8601String(),
            ],
        ]);
    }
}
