<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppUrl
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $basePath = $request->getBasePath();
        $url = $request->getSchemeAndHttpHost() . $basePath;

        config([
            'app.url' => $url,
            'app.asset_url' => $url . ($basePath == '' ? '' : '/public'),
        ]);

        return $next($request);
    }
}
