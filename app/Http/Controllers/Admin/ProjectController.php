<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProjectController extends Controller
{
    private const TECHNOLOGY_KEYS = ['key', 'name', 'icon'];

    public function index()
    {
        return Inertia::render('admin/Projects', [
            'projects' => Project::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'challenge' => 'nullable|string',
            'solution' => 'nullable|string',
            'results' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|max:4096',
            'url' => 'nullable|url|max:255',
            'repo_url' => 'nullable|url|max:255',
            'tags' => 'nullable|array',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'technologies' => 'nullable|array',
            'technologies.*.key' => 'required|string|max:80',
            'technologies.*.name' => 'required|string|max:80',
            'technologies.*.icon' => 'required|string|max:80',
            'type' => 'required|in:side_project,portfolio',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['features'] = $this->sanitizeFeatures($request->input('features'));
        $validated['technologies'] = $this->sanitizeTechnologies($request->input('technologies'));

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('projects', 'public');
        } else {
            // Keep current cover image when no new file is uploaded.
            unset($validated['image']);
        }

        $validated['gallery'] = $this->storeGalleryImages($request);
        unset($validated['gallery_images']);

        Project::create($validated);

        return back()->with('success', 'Proyek berhasil dibuat.');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'challenge' => 'nullable|string',
            'solution' => 'nullable|string',
            'results' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|max:4096',
            'url' => 'nullable|url|max:255',
            'repo_url' => 'nullable|url|max:255',
            'tags' => 'nullable|array',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'technologies' => 'nullable|array',
            'technologies.*.key' => 'required|string|max:80',
            'technologies.*.name' => 'required|string|max:80',
            'technologies.*.icon' => 'required|string|max:80',
            'type' => 'required|in:side_project,portfolio',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['features'] = $this->sanitizeFeatures($request->input('features'));
        $validated['technologies'] = $this->sanitizeTechnologies($request->input('technologies'));

        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('projects', 'public');

            if (! $stored) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;
        }

        $existingGallery = is_array($project->gallery) ? $project->gallery : [];
        $newGallery = $this->storeGalleryImages($request);
        $validated['gallery'] = array_values(array_filter(array_merge($existingGallery, $newGallery)));
        unset($validated['gallery_images']);

        $project->update($validated);

        return back()->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back()->with('success', 'Proyek berhasil dihapus.');
    }

    private function storeGalleryImages(Request $request): array
    {
        $gallery = [];

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $stored = $image->store('projects', 'public');

                if (! $stored) {
                    return [];
                }

                $gallery[] = $stored;
            }
        }

        return $gallery;
    }

    private function sanitizeFeatures($features): ?array
    {
        if (! is_array($features)) {
            return null;
        }

        $items = collect($features)
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->values()
            ->all();

        return $items !== [] ? $items : null;
    }

    private function sanitizeTechnologies($technologies): ?array
    {
        if (! is_array($technologies)) {
            return null;
        }

        $items = collect($technologies)
            ->filter(fn ($tech) => is_array($tech))
            ->map(function ($tech) {
                return collect(self::TECHNOLOGY_KEYS)
                    ->mapWithKeys(fn ($key) => [$key => trim((string) ($tech[$key] ?? ''))])
                    ->all();
            })
            ->filter(fn ($tech) => $tech['key'] !== '' && $tech['name'] !== '')
            ->values()
            ->all();

        return $items !== [] ? $items : null;
    }
}
