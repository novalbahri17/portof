<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    /** Label jenis pekerjaan (sesuai referensi UI). */
    public const EMPLOYMENT_TYPES = [
        'school_internship' => 'Magang Sekolah / Internship',
        'internship' => 'Magang Kerja',
        'full_time' => 'Penuh Waktu / Full-time',
        'part_time' => 'Paruh Waktu / Part-time',
        'contract' => 'Kontrak / Contract',
        'freelance' => 'Pekerja Lepas / Freelance',
        'remote' => 'Remote / Jarak Jauh',
    ];

    protected $fillable = [
        'type',
        'employment_type',
        'title',
        'institution',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
