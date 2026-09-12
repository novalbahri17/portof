<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Menandai versi aplikasi yang benar-benar ter-deploy.
 *
 * Masalah yang diselesaikan: ketika push ke GitHub tidak memicu rebuild,
 * situs live tetap menyajikan aset lama dan kita tidak punya cara untuk
 * membuktikannya. Dengan menulis "stempel" saat image Docker di-build,
 * kita bisa membandingkan versi live vs versi repo.
 */
class BuildInfo
{
    /**
     * Path file stempel, ditulis saat build Docker berjalan.
     */
    public static function path(): string
    {
        return base_path('build-info.json');
    }

    /**
     * Ambil isi stempel build. Null kalau belum pernah ditulis
     * (misal: dijalankan di lokal tanpa build Docker).
     *
     * @return array{commit: string, built_at: string, php: string}|null
     */
    public static function get(): ?array
    {
        $path = static::path();

        if (! File::exists($path)) {
            return null;
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Commit hash singkat yang ter-deploy, atau 'unknown'.
     */
    public static function commit(): string
    {
        return static::get()['commit'] ?? 'unknown';
    }

    /**
     * Ringkasan untuk endpoint diagnosa `/_version`.
     *
     * Sengaja hanya memuat informasi yang memang sudah publik lewat
     * `/build/manifest.json` (nama berkas ber-hash), jadi tidak ada
     * data sensitif seperti APP_KEY atau kredensial yang bocor.
     *
     * Cara pakai: bandingkan `manifest_hash` di live dengan yang di lokal.
     * Kalau sama → build terbaru sudah ter-deploy. Kalau beda → masih lama.
     *
     * @return array<string, mixed>
     */
    public static function summary(): array
    {
        $build = static::get();

        return [
            'commit' => $build['commit'] ?? 'unknown',
            'built_at' => $build['built_at'] ?? 'unknown',
            'manifest_hash' => static::manifestHash(),
            'app_asset' => static::appAsset(),
        ];
    }

    /**
     * Sidik jari isi `public/build/manifest.json`.
     *
     * Karena Vite menyisipkan hash konten ke setiap nama berkas, hash dari
     * manifest ini berubah setiap kali ada perubahan kode frontend. Ini
     * pembanding paling ringkas antara build lokal dan build yang live.
     */
    public static function manifestHash(): string
    {
        $path = public_path('build/manifest.json');

        if (! File::exists($path)) {
            return 'unknown';
        }

        return substr(sha1((string) File::get($path)), 0, 12);
    }

    /**
     * Nama berkas bundel utama (sudah ber-hash), mis. `assets/app-CKx1jFYT.js`.
     * Ini yang paling gampang dicek manual di View Source halaman live.
     */
    public static function appAsset(): ?string
    {
        $path = public_path('build/manifest.json');

        if (! File::exists($path)) {
            return null;
        }

        $decoded = json_decode((string) File::get($path), true);

        if (! is_array($decoded)) {
            return null;
        }

        foreach ($decoded as $entry => $meta) {
            if ($entry === 'resources/js/app.ts' && isset($meta['file'])) {
                return (string) $meta['file'];
            }
        }

        return null;
    }
}
