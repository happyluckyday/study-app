<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function acknowledgeKick(Request $request)
    {
        // Delete the kicked session record
        $sessionId = Session::getId();
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('is_kicked', 1)
            ->delete();

        // Log out the session in the framework
        auth()->logout();
        $request->session()->invalidate();

        return response()->json(['status' => 'acknowledged']);
    }
}
