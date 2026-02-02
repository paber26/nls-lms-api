<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => bcrypt(Str::random(16)),
            ]);
        } else {
            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
            ]);
        }

        // print_r($user->toArray()); exit;
        // $token = $user->createToken('google-token')->plainTextToken;

        // return response()->json([
        //     'token' => $token,
        //     'user' => $user
        // ]);

        $token = $user->createToken('google-token')->plainTextToken;

        // URL frontend kamu (Vite)
        // $frontendUrl = 'http://localhost:5173/oauth/callback';
        $frontendUrl = 'http://nextlevelstudy-admin.salite.site/oauth/callback';

        return redirect()->away(
            $frontendUrl . '?token=' . $token
        );
    }
}