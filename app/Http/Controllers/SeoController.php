<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(Request $request): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /settings',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(Request $request): Response
    {
        $homeLastModified = max(
            Blog::published()->max('updated_at'),
            Project::published()->max('updated_at')
        );
        $homeLastmod = $homeLastModified
            ? \Illuminate\Support\Carbon::parse($homeLastModified)->toAtomString()
            : now()->toAtomString();

        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => $homeLastmod,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
        ];

        $blogs = Blog::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderByDesc('published_at')
            ->get();

        foreach ($blogs as $blog) {
            $lastmod = ($blog->updated_at ?? $blog->published_at ?? now())->toAtomString();

            $urls[] = [
                'loc' => url("/blog/{$blog->slug}"),
                'lastmod' => $lastmod,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $projects = Project::published()
            ->select(['slug', 'updated_at'])
            ->orderBy('sort_order')
            ->get();

        foreach ($projects as $project) {
            $urls[] = [
                'loc' => url("/projects/{$project->slug}"),
                'lastmod' => ($project->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
