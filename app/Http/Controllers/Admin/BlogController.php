<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
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
            $validated['image'] = $request->file('image')->store('blogs', 'public');
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

        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('blogs', 'public');

            if (! $stored) {
                return back()->withErrors([
                    'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
                ]);
            }

            $validated['image'] = $stored;
        }

        if ($request->boolean('published') && ! $blog->published_at) {
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        return back()->with('success', 'Blog berhasil diperbarui.');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return back()->with('success', 'Blog berhasil dihapus.');
    }
}
