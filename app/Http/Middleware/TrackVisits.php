<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests to non-admin, non-asset pages
        if (
            $request->isMethod('GET')
            && ! $request->is('admin*', 'api*', 'build*', 'storage*', 'favicon*', 'robots*', 'sitemap*')
            && ! $request->ajax()
            && $response->getStatusCode() === 200
        ) {
            $this->recordVisit($request);
        }

        return $response;
    }

    private function recordVisit(Request $request): void
    {
        $ip = $request->ip();
        $ua = $request->userAgent() ?? '';

        // Throttle: max 1 visit per IP+path per 30 min
        $cacheKey = 'visit:' . md5($ip . $request->path());
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addMinutes(30));

        $geo = $this->geolocate($ip);

        Visit::create([
            'path' => '/' . ltrim($request->path(), '/'),
            'ip' => $ip,
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['countryCode'] ?? null,
            'city' => $geo['city'] ?? null,
            'device' => $this->detectDevice($ua),
            'browser' => $this->detectBrowser($ua),
            'os' => $this->detectOS($ua),
            'referrer' => $request->header('referer'),
            'user_agent' => substr($ua, 0, 500),
            'visited_at' => now(),
        ]);
    }

    private function geolocate(string $ip): array
    {
        // Skip local IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return ['country' => 'Local', 'countryCode' => 'LO', 'city' => 'Localhost'];
        }

        $cacheKey = 'geo:' . $ip;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,countryCode,city");
                if ($response->successful() && $response->json('status') !== 'fail') {
                    return $response->json();
                }
            } catch (\Throwable) {
                // Silently fail
            }
            return [];
        });
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) return 'tablet';
        if (preg_match('/mobile|android|iphone|ipod|opera mini|iemobile/i', $ua)) return 'mobile';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg/')) return 'Edge';
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Edg/')) return 'Chrome';
        if (str_contains($ua, 'Firefox/')) return 'Firefox';
        if (str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome')) return 'Safari';
        return 'Otro';
    }

    private function detectOS(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS')) return 'macOS';
        if (str_contains($ua, 'Linux') && ! str_contains($ua, 'Android')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (preg_match('/iPhone|iPad|iPod/', $ua)) return 'iOS';
        return 'Otro';
    }
}
