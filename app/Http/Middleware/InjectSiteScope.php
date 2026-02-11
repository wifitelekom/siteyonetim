<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectSiteScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->site_id && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Kullanıcı bir siteye atanmamış.');
        }

        return $next($request);
    }
}

