<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['oauth' => 'Authentication failed']);
        }

        $email = $socialUser->getEmail();
        $providerId = $socialUser->getId();
        $providerField = $provider . '_id'; // e.g. google_id, line_id

        $user = User::where($providerField, $providerId)->first();

        if (!$user) {
            if ($email) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                if ($user->password) {
                    // Challenge with password
                    session([
                        'social_bind_user_id' => $user->id,
                        'social_bind_provider' => $provider,
                        'social_bind_provider_id' => $providerId,
                    ]);
                    return redirect()->route('social.bind');
                } else {
                    // No password, cannot verify identity
                    return redirect('/login')->withErrors([
                        'email' => '此信箱已透過其他方式註冊。請使用原登入方式，或先透過「忘記密碼」設定實體密碼後再進行綁定。'
                    ]);
                }
            } else {
                // Register pure OAuth account
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $email ?? ($providerId . '@' . $provider . '.oauth'),
                    'password' => null, // Allowed by migration
                    $providerField => $providerId,
                ]);
            }
        }

        Auth::login($user, true);
        return redirect()->intended('/home');
    }

    public function showBindForm()
    {
        if (!session()->has('social_bind_user_id')) {
            return redirect('/login');
        }
        return view('auth.social_bind');
    }

    public function bind(Request $request)
    {
        $request->validate(['password' => 'required']);

        $userId = session('social_bind_user_id');
        $provider = session('social_bind_provider');
        $providerId = session('social_bind_provider_id');
        
        if (!$userId || !$provider || !$providerId) {
            return redirect('/login');
        }

        $user = User::find($userId);

        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => '密碼錯誤，無法驗證身分！']);
        }

        // Bind and login
        $providerField = $provider . '_id';
        $user->update([$providerField => $providerId]);
        
        session()->forget(['social_bind_user_id', 'social_bind_provider', 'social_bind_provider_id']);
        
        Auth::login($user, true);
        return redirect()->intended('/home');
    }
}
