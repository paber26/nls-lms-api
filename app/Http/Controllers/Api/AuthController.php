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
        $role = request()->get('role', 'user'); // default user

        return Socialite::driver('google')
            ->stateless()
            ->with([
                'state' => $role
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();
        
        // $role = request()->get('state', 'user');
            
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
        $token = $user->createToken('google-token')->plainTextToken;
        
        if ($user->role === 'admin') {
            $frontendUrl = env('FRONTEND_ADMIN_URL') . '/oauth/callback';
        } else {
            $frontendUrl = env('FRONTEND_USER_URL') . '/oauth/callback';
        }

        return redirect()->away(
            $frontendUrl . '?token=' . $token
        );
    }
}