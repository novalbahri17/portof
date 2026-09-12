<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function show(string $slug)
    {
        $project = Project::published()->where('slug', $slug)->firstOrFail();

        $relatedProjects = Project::published()
            ->where('id', '!=', $project->id)
            ->where('type', $project->type)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        $projectUrl = url("/projects/{$project->slug}");
        $seoTitle = Str::limit(trim(strip_tags($project->title)), 60);
        $seoSource = $project->description ?: strip_tags((string) ($project->challenge ?: $project->solution));
        $seoDescription = Str::limit(trim(strip_tags($seoSource)), 150);

        $tags = collect($project->tags ?? [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter();

        $technologyNames = collect($project->technologies ?? [])
            ->map(fn ($technology) => trim((string) ($technology['name'] ?? '')))
            ->filter();

        $keywordsList = $tags
            ->concat($technologyNames)
            ->unique()
            ->values();

        $primaryGalleryImage = is_array($project->gallery) && count($project->gallery) > 0
            ? $project->gallery[0]
            : null;

        $projectPrimaryImage = $project->image ?: $primaryGalleryImage;
        $siteOgImage = SiteSetting::get('og_image', '');
        $siteTwitterImage = SiteSetting::get('twitter_image', '');

        $ogImage = $this->resolveImageUrl($projectPrimaryImage ?: ($siteOgImage ?: $siteTwitterImage));
        $twitterImage = $this->resolveImageUrl($projectPrimaryImage ?: ($siteTwitterImage ?: $siteOgImage));

        $siteName = SiteSetting::get('seo_title', config('app.name'));
        $sectionLabel = $project->type === 'side_project' ? 'Side Projects' : 'Portofolio';
        $sectionAnchor = $project->type === 'side_project' ? '/#side-projects' : '/#portfolio';
        $schemaType = $project->type === 'side_project' ? 'SoftwareApplication' : 'CreativeWork';

        $mainEntity = [
            '@type' => $schemaType,
            'name' => $project->title,
            'description' => $seoDescription,
            'url' => $projectUrl,
            'datePublished' => $project->created_at?->toIso8601String(),
            'dateModified' => $project->updated_at?->toIso8601String(),
            'keywords' => $keywordsList->implode(', '),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $projectUrl,
            ],
        ];

        if ($ogImage) {
            $mainEntity['image'] = [$ogImage];
        }

        if ($project->type === 'side_project') {
            $mainEntity['applicationCategory'] = 'WebApplication';
            $mainEntity['operatingSystem'] = 'Any';
        }

        if ($technologyNames->isNotEmpty()) {
            $mainEntity['about'] = $technologyNames
                ->map(fn ($name) => [
                    '@type' => 'Thing',
                    'name' => $name,
                ])
                ->values()
                ->all();
        }

        $structuredData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => 'Beranda',
                            'item' => url('/'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => $sectionLabel,
                            'item' => url($sectionAnchor),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 3,
                            'name' => $project->title,
                            'item' => $projectUrl,
                        ],
                    ],
                ],
                $mainEntity,
            ],
        ];

        return Inertia::render('Project', [
            'project' => $project,
            'relatedProjects' => $relatedProjects,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'keywords' => $keywordsList->implode(', '),
                'canonical' => $projectUrl,
                'ogTitle' => $seoTitle,
                'ogDescription' => $seoDescription,
                'ogImage' => $ogImage ?: '',
                'ogType' => 'article',
                'twitterCard' => 'summary_large_image',
                'twitterTitle' => $seoTitle,
                'twitterDescription' => $seoDescription,
                'twitterImage' => $twitterImage ?: '',
                'favicon' => SiteSetting::get('favicon', ''),
                'publishedAt' => $project->created_at?->toIso8601String(),
                'modifiedAt' => $project->updated_at?->toIso8601String(),
                'siteName' => $siteName,
                'structuredData' => json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ]);
    }

    private function resolveImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return asset("storage/{$image}");
    }
}
