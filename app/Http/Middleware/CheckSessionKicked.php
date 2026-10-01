<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CheckSessionKicked
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = Session::getId();
        
        $session = DB::table('sessions')->where('id', $sessionId)->first();
        
        if ($session && $session->is_kicked) {
            // Passive Interception: If API request, return 409
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'kicked_out', 
                    'message' => '您的帳號已在其他裝置登入'
                ], 409);
            }
            
            // For normal web request
            Auth::logout();
            $request->session()->invalidate();
            return redirect('/login')->withErrors(['kicked' => '您的帳號已在其他裝置登入']);
        }

        return $next($request);
    }
}
