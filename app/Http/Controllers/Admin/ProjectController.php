<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\StorageFiles;
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
            $stored = StorageFiles::store($request->file('image'), 'projects');

            if ($stored === null) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;
        } else {
            // Keep current cover image when no new file is uploaded.
            unset($validated['image']);
        }

        $gallery = $this->storeGalleryImages($request);

        if ($gallery === null) {
            return back()->withErrors([
                'gallery_images' => 'Ada gambar galeri yang gagal disimpan. Coba unggah ulang.',
            ]);
        }

        $validated['gallery'] = $gallery;
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

        $missing = '';
        $oldCoverToDelete = null;

        // PENTING: form yang tidak mengunggah gambar baru tetap mengirim
        // field `image` kosong. Karena aturannya `nullable`, nilai kosong itu
        // lolos validasi dan akan menimpa gambar lama dengan null.
        // Buang dulu; nanti diisi hanya kalau ada berkas baru yang benar
        // benar tersimpan.
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $stored = StorageFiles::store($request->file('image'), 'projects');

            if ($stored === null) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;

            if ($project->image && $project->image !== $stored) {
                // Hapus nanti, setelah data baru benar-benar tersimpan.
                $oldCoverToDelete = $project->image;
            }
        } elseif ($project->image && ! StorageFiles::exists($project->image)) {
            // Gambar utama tidak ada di disk ini. JANGAN hapus referensinya:
            // database mungkin dipakai bersama lingkungan lain.
            $missing = ' Gambar utama tidak ada di server ini — unggah ulang kalau perlu.';
        }

        $existingGallery = is_array($project->gallery) ? $project->gallery : [];

        $newGallery = $this->storeGalleryImages($request);

        if ($newGallery === null) {
            return back()->withErrors([
                'gallery_images' => 'Ada gambar galeri yang gagal disimpan. Coba unggah ulang.',
            ]);
        }

        // Galeri lama tetap dipertahankan seluruhnya. Gambar baru hanya
        // ditambahkan, tidak pernah menggantikan yang sudah ada.
        $validated['gallery'] = array_values(array_unique(array_merge($existingGallery, $newGallery)));
        unset($validated['gallery_images']);

        $project->update($validated);

        // Baru sekarang aman membuang berkas lama yang sudah digantikan.
        StorageFiles::delete($oldCoverToDelete);

        return back()->with('success', 'Proyek berhasil diperbarui.'.$missing);
    }

    public function destroy(Project $project)
    {
        $files = array_merge(
            $project->image ? [$project->image] : [],
            is_array($project->gallery) ? $project->gallery : [],
        );

        $project->delete();

        StorageFiles::deleteMany($files);

        return back()->with('success', 'Proyek berhasil dihapus.');
    }

    /**
     * Simpan gambar galeri. Mengembalikan null kalau ada yang gagal, supaya
     * pemanggil bisa membatalkan tanpa kehilangan data lama. Array kosong
     * berarti memang tidak ada berkas galeri yang dikirim.
     *
     * @return array<int, string>|null
     */
    private function storeGalleryImages(Request $request): ?array
    {
        if (! $request->hasFile('gallery_images')) {
            return [];
        }

        return StorageFiles::storeMany($request->file('gallery_images', []) ?? [], 'projects');
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
