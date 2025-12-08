<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixViteUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fix untuk reverse proxy: jangan gunakan port 443 untuk HTTPS
        if ($request->getPort() == 443 && $request->getScheme() == 'https') {
            // Override server port untuk tidak ditampilkan di URL
            // Set port ke 80 (default HTTP) agar Laravel tidak menambahkan port di URL
            $request->server->set('SERVER_PORT', '80');
            $request->headers->remove('X-Forwarded-Port');
        }

        return $next($request);
    }
}
