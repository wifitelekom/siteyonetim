<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\SiteContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectSiteScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            $activeSiteId = SiteContext::resolveForUser($user);

            if ($activeSiteId && !Site::query()->whereKey($activeSiteId)->exists()) {
                SiteContext::set($request, null);
                $activeSiteId = null;
            }

            // Policies/controllers read site_id directly; reflect active context at request level.
            $user->setAttribute('site_id', $activeSiteId);

            if (!$activeSiteId && !$request->routeIs('api.super.sites.*')) {
                abort(403, 'Super admin icin once bir site secin.');
            }

            return $next($request);
        }

        if (!$user->site_id) {
            abort(403, 'Kullanici bir siteye atanmamis.');
        }

        return $next($request);
    }
}
