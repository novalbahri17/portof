<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificationController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Certifications', [
            'certifications' => Certification::latest()->get()->map(fn (Certification $certification) => [
                'id' => $certification->id,
                'title' => $certification->title,
                'description' => $certification->description,
                'images' => $certification->imageList(),
                'certificate_file' => $certification->certificate_file,
                'published' => $certification->published,
                'sort_order' => $certification->sort_order,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:2048',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $paths = [];
        foreach ($request->file('images', []) as $file) {
            $paths[] = $file->store('certifications/images', 'public');
        }

        $certification = Certification::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'images' => $paths,
            'image' => $paths[0] ?? null,
            'certificate_file' => $request->hasFile('certificate_file')
                ? $request->file('certificate_file')->store('certifications/pdfs', 'public')
                : null,
            'published' => $validated['published'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Sertifikasi berhasil dibuat.');
    }

    public function update(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'keep_images' => 'nullable|array',
            'keep_images.*' => 'string',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $existing = $certification->imageList();
        $keep = $request->input('keep_images', $existing);

        // Hapus berkas yang dibuang dari galeri
        foreach (array_diff($existing, $keep) as $removed) {
            if (Storage::disk('public')->exists($removed)) {
                Storage::disk('public')->delete($removed);
            }
        }

        $paths = array_values(array_intersect($existing, $keep));

        // Tambahkan gambar baru
        foreach ($request->file('images', []) as $file) {
            $paths[] = $file->store('certifications/images', 'public');
        }

        if ($paths === []) {
            return back()->withErrors([
                'images' => 'Sertifikasi harus memiliki minimal satu gambar.',
            ]);
        }

        $certification->title = $validated['title'];
        $certification->description = $validated['description'];
        $certification->images = $paths;
        $certification->image = $paths[0];
        $certification->published = $validated['published'] ?? false;
        $certification->sort_order = $validated['sort_order'] ?? 0;

        if ($request->hasFile('certificate_file')) {
            if ($certification->certificate_file && Storage::disk('public')->exists($certification->certificate_file)) {
                Storage::disk('public')->delete($certification->certificate_file);
            }

            $certification->certificate_file = $request->file('certificate_file')->store('certifications/pdfs', 'public');
        }

        $certification->save();

        return back()->with('success', 'Sertifikasi berhasil diperbarui.');
    }

    public function destroy(Certification $certification)
    {
        foreach ($certification->imageList() as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($certification->certificate_file && Storage::disk('public')->exists($certification->certificate_file)) {
            Storage::disk('public')->delete($certification->certificate_file);
        }

        $certification->delete();

        return back()->with('success', 'Sertifikasi berhasil dihapus.');
    }
}
