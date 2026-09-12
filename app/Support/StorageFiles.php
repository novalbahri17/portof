<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Pembantu penyimpanan berkas unggahan (gambar & PDF).
 *
 * Aturan utama modul ini:
 *  1. Berkas baru SELALU disimpan lebih dulu dan diverifikasi ada di disk.
 *  2. Berkas lama baru boleh dihapus setelah berkas baru benar-benar aman.
 *  3. Referensi path di database TIDAK PERNAH dihapus otomatis hanya karena
 *     berkasnya tidak terlihat di disk. Database aplikasi ini dipakai bersama
 *     oleh lingkungan lokal dan server produksi, jadi berkas yang "hilang" di
 *     satu mesin bisa saja masih ada di mesin lain. Kita hanya melapor.
 */
class StorageFiles
{
    /**
     * Disk tempat semua berkas unggahan disimpan.
     */
    public static function disk(): string
    {
        return 'public';
    }

    /**
     * Simpan satu berkas unggahan.
     *
     * @return string|null Path relatif kalau berhasil, null kalau gagal.
     */
    public static function store(?UploadedFile $file, string $folder): ?string
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($folder, self::disk());

        // Verifikasi: jangan pernah melaporkan berhasil kalau berkasnya
        // ternyata tidak ada di disk.
        if (! is_string($path) || $path === '' || ! self::exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Simpan beberapa berkas sekaligus.
     *
     * Mengembalikan null kalau ADA satu saja yang gagal, supaya pemanggil bisa
     * membatalkan seluruh proses tanpa kehilangan berkas lama. Array kosong
     * berarti memang tidak ada berkas yang perlu disimpan.
     *
     * @param  array<int|string, UploadedFile>  $files
     * @return array<int, string>|null
     */
    public static function storeMany(array $files, string $folder): ?array
    {
        $paths = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $stored = self::store($file, $folder);

            if ($stored === null) {
                // Jangan tinggalkan berkas setengah jadi di disk.
                self::deleteMany($paths);

                return null;
            }

            $paths[] = $stored;
        }

        return $paths;
    }

    /**
     * Apakah path ini benar-benar ada di disk?
     */
    public static function exists(?string $path): bool
    {
        return is_string($path) && $path !== ''
            && Storage::disk(self::disk())->exists($path);
    }

    /**
     * Hapus berkas, tapi hanya kalau memang ada. Tidak pernah melempar error.
     */
    public static function delete(?string $path): void
    {
        if (self::exists($path)) {
            Storage::disk(self::disk())->delete($path);
        }
    }

    /**
     * Hapus banyak berkas sekaligus.
     *
     * @param  array<int, string>  $paths
     */
    public static function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            self::delete($path);
        }
    }

    /**
     * Pisahkan path yang masih ada di disk dari yang sudah hilang.
     *
     * @param  array<int, string>  $paths
     * @return array{0: array<int, string>, 1: array<int, string>}
     *                                                           [0] = masih ada, [1] = hilang
     */
    public static function partition(array $paths): array
    {
        $ada = [];
        $hilang = [];

        foreach (array_unique($paths) as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            if (self::exists($path)) {
                $ada[] = $path;
            } else {
                $hilang[] = $path;
            }
        }

        return [array_values($ada), array_values($hilang)];
    }

    /**
     * Susun pesan flash dengan informasi berkas yang hilang.
     */
    public static function missingMessage(array $missing, string $label = 'berkas'): string
    {
        $total = count($missing);

        if ($total === 0) {
            return '';
        }

        return ' '.$total.' '.$label.' tidak ada di server ini. Tautannya tetap disimpan'
            .' — unggah ulang hanya kalau berkas itu memang perlu diganti.';
    }
}
