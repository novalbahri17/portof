<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddRobotsTagHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET')) {
            return $response;
        }

        if ($response->headers->has('X-Robots-Tag')) {
            return $response;
        }

        if (! $this->isHtmlResponse($response)) {
            return $response;
        }

        if ($this->isPrivateArea($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            return $response;
        }

        $response->headers->set('X-Robots-Tag', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');

        return $response;
    }

    private function isHtmlResponse(Response $response): bool
    {
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        return str_contains($contentType, 'text/html') || str_contains($contentType, 'application/xhtml+xml');
    }

    private function isPrivateArea(Request $request): bool
    {
        return $request->is(
            'admin*',
            'settings*',
            'login',
            'register',
            'forgot-password',
            'reset-password*',
            'verify-email*',
            'email/verification*',
            'confirm-password',
            'two-factor-challenge'
        );
    }
}
