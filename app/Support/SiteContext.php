<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class SiteContext
{
    public const SESSION_KEY = 'super_admin_site_id';

    public static function resolveForUser(?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        if ($user->hasRole('super-admin')) {
            $siteId = session(self::SESSION_KEY);
            return is_numeric($siteId) ? (int) $siteId : null;
        }

        return $user->site_id ? (int) $user->site_id : null;
    }

    public static function set(Request $request, ?int $siteId): void
    {
        if ($siteId) {
            $request->session()->put(self::SESSION_KEY, $siteId);
            return;
        }

        $request->session()->forget(self::SESSION_KEY);
    }
}
