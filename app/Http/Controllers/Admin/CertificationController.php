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
            'certifications' => Certification::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|max:2048',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['image'] = $request->file('image')->store('certifications/images', 'public');

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('certifications/pdfs', 'public');
        }

        Certification::create($validated);

        return back()->with('success', 'Sertifikasi berhasil dibuat.');
    }

    public function update(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            if ($certification->image && Storage::disk('public')->exists($certification->image)) {
                Storage::disk('public')->delete($certification->image);
            }

            $validated['image'] = $request->file('image')->store('certifications/images', 'public');
        }

        if ($request->hasFile('certificate_file')) {
            if ($certification->certificate_file && Storage::disk('public')->exists($certification->certificate_file)) {
                Storage::disk('public')->delete($certification->certificate_file);
            }

            $validated['certificate_file'] = $request->file('certificate_file')->store('certifications/pdfs', 'public');
        }

        $certification->update($validated);

        return back()->with('success', 'Sertifikasi berhasil diperbarui.');
    }

    public function destroy(Certification $certification)
    {
        if ($certification->image && Storage::disk('public')->exists($certification->image)) {
            Storage::disk('public')->delete($certification->image);
        }

        if ($certification->certificate_file && Storage::disk('public')->exists($certification->certificate_file)) {
            Storage::disk('public')->delete($certification->certificate_file);
        }

        $certification->delete();

        return back()->with('success', 'Sertifikasi berhasil dihapus.');
    }
}
