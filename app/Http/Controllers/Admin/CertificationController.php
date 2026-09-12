<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Support\StorageFiles;
use Illuminate\Http\Request;
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

        $paths = StorageFiles::storeMany($request->file('images', []) ?? [], 'certifications/images');

        if ($paths === null || $paths === []) {
            return back()->withErrors([
                'images' => 'Gambar gagal disimpan. Coba unggah ulang.',
            ]);
        }

        $certificate = null;

        if ($request->hasFile('certificate_file')) {
            $certificate = StorageFiles::store($request->file('certificate_file'), 'certifications/pdfs');

            if ($certificate === null) {
                // Batalkan semuanya: jangan simpan gambar tanpa PDF yang diminta.
                StorageFiles::deleteMany($paths);

                return back()->withErrors([
                    'certificate_file' => 'PDF gagal disimpan. Coba unggah ulang.',
                ]);
            }
        }

        Certification::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'images' => $paths,
            'image' => $paths[0],
            'certificate_file' => $certificate,
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

        $keep = $request->input('keep_images');

        if (! is_array($keep)) {
            // Form tidak mengirim daftar gambar yang dipertahankan. Anggap
            // semuanya dipertahankan — jangan pernah menghapus gambar hanya
            // karena datanya tidak ikut terkirim.
            $kept = $existing;
        } else {
            // Pengguna memang memilih gambar mana yang disimpan.
            $kept = array_values(array_intersect($existing, $keep));
        }

        // Informasi saja: gambar lama yang berkasnya tidak terlihat di disk
        // ini. Referensinya TIDAK dihapus karena database aplikasi ini dipakai
        // bersama dengan server produksi — berkas itu bisa saja ada di sana.
        [, $missing] = StorageFiles::partition($kept);

        // Unggah gambar baru DULU dan pastikan tersimpan.
        $newPaths = StorageFiles::storeMany($request->file('images', []) ?? [], 'certifications/images');

        if ($newPaths === null) {
            return back()->withErrors([
                'images' => 'Ada gambar yang gagal disimpan. Coba unggah ulang.',
            ]);
        }

        $paths = array_values(array_unique(array_merge($kept, $newPaths)));

        if ($paths === []) {
            return back()->withErrors([
                'images' => 'Sertifikasi harus memiliki minimal satu gambar. Silakan pilih gambar baru.',
            ]);
        }

        $oldPdfToDelete = null;
        $pdfMissing = false;

        if ($request->hasFile('certificate_file')) {
            $pdf = StorageFiles::store($request->file('certificate_file'), 'certifications/pdfs');

            if ($pdf === null) {
                // Gagal menyimpan PDF baru: batalkan seluruh perubahan,
                // jangan sampai PDF lama ikut hilang tanpa pengganti.
                return back()->withErrors([
                    'certificate_file' => 'PDF gagal disimpan. Coba unggah ulang.',
                ]);
            }

            if ($certification->certificate_file && $certification->certificate_file !== $pdf) {
                $oldPdfToDelete = $certification->certificate_file;
            }

            $certification->certificate_file = $pdf;
        } elseif ($certification->certificate_file && ! StorageFiles::exists($certification->certificate_file)) {
            // PDF lama tidak ada di disk ini. JANGAN hapus referensinya:
            // database mungkin dipakai bersama oleh lingkungan lain yang
            // berkasnya ada. Cukup beri tahu pengguna.
            $pdfMissing = true;
        }

        $certification->title = $validated['title'];
        $certification->description = $validated['description'];
        $certification->images = $paths;
        $certification->image = $paths[0];
        $certification->published = $validated['published'] ?? false;
        $certification->sort_order = $validated['sort_order'] ?? 0;
        $certification->save();

        // Baru sekarang aman menghapus berkas yang memang dibuang.
        // Penghapusan hanya berjalan untuk berkas yang masih ada.
        StorageFiles::deleteMany(array_values(array_diff($existing, $paths)));
        StorageFiles::delete($oldPdfToDelete);

        $message = 'Sertifikasi berhasil diperbarui.';
        $message .= StorageFiles::missingMessage($missing, 'gambar');

        if ($pdfMissing) {
            $message .= ' PDF diploma tidak ada di server ini — unggah ulang kalau memang perlu.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Certification $certification)
    {
        StorageFiles::deleteMany($certification->imageList());
        StorageFiles::delete($certification->certificate_file);

        $certification->delete();

        return back()->with('success', 'Sertifikasi berhasil dihapus.');
    }
}
