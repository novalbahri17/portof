<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        $days = 30;
        $from = now()->subDays($days)->startOfDay();

        // Visitas por día (últimos 30 días)
        $visitsPerDay = Visit::where('visited_at', '>=', $from)
            ->select(DB::raw("DATE(visited_at) as date"), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Rellenar días sin visitas con 0
        $dailyVisits = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyVisits[] = [
                'date' => $date,
                'total' => $visitsPerDay[$date] ?? 0,
            ];
        }

        // Top países
        $countries = Visit::whereNotNull('country')
            ->select('country', 'country_code', DB::raw('COUNT(*) as total'))
            ->groupBy('country', 'country_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Dispositivos
        $devices = Visit::select('device', DB::raw('COUNT(*) as total'))
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        // Navegadores
        $browsers = Visit::select('browser', DB::raw('COUNT(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Sistemas operativos
        $os = Visit::select('os', DB::raw('COUNT(*) as total'))
            ->groupBy('os')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Páginas más visitadas
        $topPages = Visit::select('path', DB::raw('COUNT(*) as total'))
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Visitas recientes
        $recentVisits = Visit::orderByDesc('visited_at')
            ->limit(20)
            ->get(['path', 'country', 'country_code', 'city', 'device', 'browser', 'os', 'visited_at']);

        // Totales
        $totalVisits = Visit::count();
        $todayVisits = Visit::whereDate('visited_at', today())->count();
        $weekVisits = Visit::where('visited_at', '>=', now()->subWeek())->count();
        $uniqueCountries = Visit::distinct('country_code')->count('country_code');

        return Inertia::render('admin/Analytics', [
            'dailyVisits' => $dailyVisits,
            'countries' => $countries,
            'devices' => $devices,
            'browsers' => $browsers,
            'os' => $os,
            'topPages' => $topPages,
            'recentVisits' => $recentVisits,
            'totalVisits' => $totalVisits,
            'todayVisits' => $todayVisits,
            'weekVisits' => $weekVisits,
            'uniqueCountries' => $uniqueCountries,
        ]);
    }
}
