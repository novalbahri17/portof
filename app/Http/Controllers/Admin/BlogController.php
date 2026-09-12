<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\StorageFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Blogs', [
            'blogs' => Blog::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['excerpt'] = isset($validated['excerpt']) ? trim(strip_tags($validated['excerpt'])) : null;

        if ($request->hasFile('image')) {
            $stored = StorageFiles::store($request->file('image'), 'blogs');

            if ($stored === null) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;
        }

        if ($request->boolean('published')) {
            $validated['published_at'] = now();
        }

        Blog::create($validated);

        return back()->with('success', 'Blog berhasil dibuat.');
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['excerpt'] = isset($validated['excerpt']) ? trim(strip_tags($validated['excerpt'])) : null;

        // PENTING: form yang tidak mengunggah gambar baru tetap mengirim
        // field `image` kosong. Karena aturannya `nullable`, nilai kosong itu
        // lolos validasi dan akan menimpa gambar lama dengan null.
        // Buang dulu; nanti diisi hanya kalau ada berkas baru yang benar
        // benar tersimpan.
        unset($validated['image']);

        $missing = '';
        $oldImageToDelete = null;

        if ($request->hasFile('image')) {
            $stored = StorageFiles::store($request->file('image'), 'blogs');

            if ($stored === null) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;

            if ($blog->image && $blog->image !== $stored) {
                // Hapus nanti, setelah data baru benar-benar tersimpan.
                $oldImageToDelete = $blog->image;
            }
        } elseif ($blog->image && ! StorageFiles::exists($blog->image)) {
            // Gambar lama tidak ada di disk ini. JANGAN hapus referensinya:
            // database mungkin dipakai bersama lingkungan lain.
            $missing = ' Gambar lama tidak ada di server ini — unggah ulang kalau perlu.';
        }

        if ($request->boolean('published') && ! $blog->published_at) {
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        // Baru sekarang aman membuang berkas lama yang sudah digantikan.
        StorageFiles::delete($oldImageToDelete);

        return back()->with('success', 'Blog berhasil diperbarui.'.$missing);
    }

    public function destroy(Blog $blog)
    {
        $image = $blog->image;

        $blog->delete();

        StorageFiles::delete($image);

        return back()->with('success', 'Blog berhasil dihapus.');
    }

    /**
     * Buang gambar utama blog ini sekarang juga (dari disk dan database).
     */
    public function deleteImage(Blog $blog)
    {
        $image = $blog->image;

        // Kosongkan dulu referensinya, baru buang berkasnya.
        $blog->update(['image' => null]);

        StorageFiles::delete($image);

        return back()->with('success', 'Gambar utama blog dihapus.');
    }
}
