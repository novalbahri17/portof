<?php

namespace App\Http\Controllers;

use App\Support\BuildInfo;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint diagnosa deploy: `GET /_version`.
 *
 * Dipakai untuk menjawab pertanyaan "situs live sudah pakai build terbaru
 * atau belum?" dengan cara membandingkan commit & sidik jari aset.
 *
 * Sengaja dibuat sebagai controller (bukan closure di routes/web.php)
 * karena `php artisan route:cache` tidak bisa meng-cache closure route.
 */
class BuildVersionController extends Controller
{
    /**
     * Kembalikan info build yang sedang live.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(BuildInfo::summary());
    }
}
