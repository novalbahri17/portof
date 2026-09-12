<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path', 'ip', 'country', 'country_code', 'city',
        'device', 'browser', 'os', 'referrer', 'user_agent', 'visited_at',
    ];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime'];
    }
}
