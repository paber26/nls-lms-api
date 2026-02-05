<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use PhpParser\Node\Expr\Print_;

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
        
        // print_r('oke'); exit;
        // print_r($googleUser->toArray()); exit;
            
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

        // URL frontend dari ENV
        $frontendUrl = rtrim(config('app.frontend_url'), '/') . '/oauth/callback';
        // print($frontendUrl); exit;

        // print_r($user->toArray()); exit;

        return redirect()->away(
            $frontendUrl . '?token=' . $token
        );
    }
}