<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SiteSettingController extends Controller
{
    private const SEO_KEYS = [
        'seo_title', 'seo_description', 'seo_keywords', 'seo_canonical',
        'og_title', 'og_description', 'og_image', 'og_type',
        'twitter_card', 'twitter_title', 'twitter_description', 'twitter_image',
        'favicon',
    ];

    /**
     * Kunci pengaturan yang berisi path berkas gambar.
     * Hanya boleh diubah lewat uploadSeoImage() / deleteSeoImage().
     */
    private const IMAGE_KEYS = [
        'hero_image', 'logo_light', 'favicon', 'og_image', 'twitter_image',
    ];

    public function edit()
    {
        $settings = [
            'hero_title' => SiteSetting::get('hero_title', 'Halo, saya Seorang Developer Laravel'),
            'hero_subtitle' => SiteSetting::get('hero_subtitle', 'Membangun pengalaman web dengan kode yang bersih dan desain yang fungsional.'),
            'hero_badge' => SiteSetting::get('hero_badge', 'Full Stack Developer'),
            'hero_image' => SiteSetting::get('hero_image', ''),
            'hero_image_shape' => SiteSetting::get('hero_image_shape', 'circle'),
            'hero_image_size' => SiteSetting::get('hero_image_size', '112'),
            'about' => SiteSetting::get('about', ''),
            'hobbies' => SiteSetting::get('hobbies', ''),
            'social_github' => SiteSetting::get('social_github', ''),
            'social_linkedin' => SiteSetting::get('social_linkedin', ''),
            'social_twitter' => SiteSetting::get('social_twitter', ''),
            'social_instagram' => SiteSetting::get('social_instagram', ''),
            'social_youtube' => SiteSetting::get('social_youtube', ''),
            'social_tiktok' => SiteSetting::get('social_tiktok', ''),
            'social_discord' => SiteSetting::get('social_discord', ''),
            'social_website' => SiteSetting::get('social_website', ''),
            'section_about_visible' => SiteSetting::get('section_about_visible', '1'),
            'section_projects_visible' => SiteSetting::get('section_projects_visible', '1'),
            'section_portfolio_visible' => SiteSetting::get('section_portfolio_visible', '1'),
            'section_certifications_visible' => SiteSetting::get('section_certifications_visible', '1'),
            'section_experience_visible' => SiteSetting::get('section_experience_visible', '1'),
            'section_blog_visible' => SiteSetting::get('section_blog_visible', '1'),
            'section_contact_visible' => SiteSetting::get('section_contact_visible', '1'),
            'logo_light' => SiteSetting::get('logo_light', ''),
        ];

        foreach (self::SEO_KEYS as $key) {
            $settings[$key] = SiteSetting::get($key, '');
        }

        return Inertia::render('admin/SiteSettings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:500',
            'hero_subtitle' => 'nullable|string|max:1000',
            'hero_badge' => 'nullable|string|max:200',
            'hero_image_shape' => 'nullable|string|in:circle,square,rounded',
            'hero_image_size' => 'nullable|integer|min:64|max:240',
            'about' => 'nullable|string|max:50000',
            'hobbies' => 'nullable|string|max:50000',
            'social_github' => 'nullable|url|max:500',
            'social_linkedin' => 'nullable|url|max:500',
            'social_twitter' => 'nullable|url|max:500',
            'social_instagram' => 'nullable|url|max:500',
            'social_youtube' => 'nullable|url|max:500',
            'social_tiktok' => 'nullable|url|max:500',
            'social_discord' => 'nullable|url|max:500',
            'social_website' => 'nullable|url|max:500',
            'section_about_visible' => 'nullable|string|in:0,1',
            'section_projects_visible' => 'nullable|string|in:0,1',
            'section_portfolio_visible' => 'nullable|string|in:0,1',
            'section_certifications_visible' => 'nullable|string|in:0,1',
            'section_experience_visible' => 'nullable|string|in:0,1',
            'section_blog_visible' => 'nullable|string|in:0,1',
            'section_contact_visible' => 'nullable|string|in:0,1',
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_canonical' => 'nullable|url|max:500',
            'og_title' => 'nullable|string|max:200',
            'og_description' => 'nullable|string|max:500',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|in:summary,summary_large_image',
            'twitter_title' => 'nullable|string|max:200',
            'twitter_description' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            // Kolom gambar diurus lewat uploadSeoImage/deleteSeoImage.
            // Jangan pernah ditimpa dari form biasa, supaya gambar tidak
            // terhapus atau tertimpa saat pengguna hanya mengedit teks.
            if (in_array($key, self::IMAGE_KEYS, true)) {
                continue;
            }

            SiteSetting::set($key, $value ?? '');
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }

    public function uploadSeoImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'type' => 'required|string|in:og_image,twitter_image,favicon,hero_image,logo_light',
        ]);

        $type = $request->input('type');

        // Simpan berkas baru dulu. Berkas lama BARU dihapus setelah
        // berkas baru benar-benar tersimpan, supaya gambar tidak bisa
        // hilang kalau proses unggah gagal di tengah jalan.
        $folder = str_starts_with($type, 'logo') ? 'logo' : 'seo';
        $path = $request->file('image')->store($folder, 'public');

        if (! $path) {
            return back()->withErrors([
                'image' => 'Gambar gagal disimpan. Coba unggah ulang.',
            ]);
        }

        $old = SiteSetting::get($type, '');

        SiteSetting::set($type, $path);

        if ($old && $old !== $path && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        return back()->with('success', 'Gambar berhasil diperbarui.');
    }

    public function deleteSeoImage(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:og_image,twitter_image,favicon,hero_image,logo_light',
        ]);

        $type = $request->input('type');
        $old = SiteSetting::get($type, '');

        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        SiteSetting::set($type, '');

        return back()->with('success', 'Gambar berhasil dihapus.');
    }
}
