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
                // Auto-bind
                $user->update([$providerField => $providerId]);
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
}
