<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content-Security-Policy (CSP) - защита от XSS
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://mc.yandex.ru https://yandex.ru https://code.jivosite.com https://*.jivo.ru https://*.jivosite.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data: https: http:; " .
            "connect-src 'self' https://mc.yandex.ru https://*.jivo.ru https://*.jivosite.com; " .
            "frame-src https://money.yandex.ru https://yoomoney.ru https://secure.platima.net; " .
            "object-src 'none'; " .
            "base-uri 'self';"
        );

        // X-Content-Type-Options - предотвращает MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options - защита от clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer-Policy - контроль передачи referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy - ограничение API браузера
        $response->headers->set('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=(), payment=(self)'
        );

        return $response;
    }
}
