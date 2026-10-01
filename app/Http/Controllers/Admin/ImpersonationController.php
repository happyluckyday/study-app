<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ImpersonationController extends Controller
{
    public function impersonate($userId)
    {
        // Store original admin ID to return later
        Session::put('impersonator_id', Auth::id());
        
        // Signal the Login listener to bypass kick-old
        Session::put('is_impersonating', true);
        
        Auth::loginUsingId($userId);
        
        // Mark session as shadow in the database
        DB::table('sessions')->where('id', Session::getId())->update(['is_shadow' => 1]);
        
        return redirect('/home')->with('status', '您正在模擬使用者 ' . $userId);
    }

    public function leave()
    {
        if (Session::has('impersonator_id')) {
            $adminId = Session::pull('impersonator_id');
            Session::forget('is_impersonating');
            Auth::loginUsingId($adminId);
            return redirect('/home')->with('status', '已結束模擬');
        }
        
        return redirect('/home');
    }
}
