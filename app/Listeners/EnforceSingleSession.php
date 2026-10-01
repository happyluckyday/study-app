<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class EnforceSingleSession
{
    public function handle(Login $event): void
    {
        // Bypass for shadow sessions (Admin Impersonation)
        if (Session::get('is_impersonating')) {
            return;
        }

        // 1. Kick old sessions
        $currentSessionId = Session::getId();
        DB::table('sessions')
            ->where('user_id', $event->user->id)
            ->where('id', '!=', $currentSessionId)
            ->update(['is_kicked' => 1]);

        // 2. Revoke Remember Token to prevent old devices from auto-login
        $newToken = $event->remember ? Str::random(60) : null;
        $event->user->forceFill(['remember_token' => $newToken])->save();

        if ($event->remember) {
            // Re-queue the recaller cookie with the new token
            $guard = Auth::guard();
            // password hash is used in Laravel recaller cookie
            $cookieValue = $event->user->getAuthIdentifier().'|'.$newToken.'|'.$event->user->getAuthPassword();
            $cookie = cookie(
                $guard->getRecallerName(),
                $cookieValue,
                43200 // 5 years in minutes
            );
            Cookie::queue($cookie);
        }
    }
}
