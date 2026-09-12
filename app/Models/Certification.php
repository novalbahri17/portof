<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'images',
        'certificate_file',
        'published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    /**
     * Selalu mengembalikan daftar gambar sebagai array, walaupun datanya
     * masih memakai kolom `image` tunggal (data lama).
     *
     * @return array<int, string>
     */
    public function imageList(): array
    {
        $raw = $this->attributes['images'] ?? null;
        $images = is_string($raw) ? json_decode($raw, true) : $raw;

        if (is_array($images) && $images !== []) {
            return $this->cleanPaths($images);
        }

        $legacy = $this->attributes['image'] ?? null;

        if (is_string($legacy) && $legacy !== '') {
            $decoded = json_decode($legacy, true);

            return $this->cleanPaths(is_array($decoded) ? $decoded : [$legacy]);
        }

        return [];
    }

    /**
     * Akses `$certification->images` selalu berupa array.
     */
    protected function images(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->imageList(),
            set: fn ($value): string => json_encode($this->cleanPaths((array) $value)),
        );
    }

    /**
     * @param  array<int, mixed>  $paths
     * @return array<int, string>
     */
    private function cleanPaths(array $paths): array
    {
        return array_values(array_filter($paths, fn ($path) => is_string($path) && $path !== ''));
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
