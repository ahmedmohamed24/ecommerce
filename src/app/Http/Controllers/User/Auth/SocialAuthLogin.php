<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Log;
use Str;

class SocialAuthLogin extends Controller
{
    use JsonResponse;

    public function redirectToProvider(string $driver)
    {
        //validate available drivers
        return Socialite::driver($driver)->redirect();
    }

    public function handleProviderCallback(string $driver)
    {
        try {
            $user = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            Log::alert($e);

            return \redirect("/auth/{$driver}/login");
        }
        $authUser = $this->findOrCreateUser($user);
        $token = Auth::login($authUser, \true);

        return $this->response(
            'success',
            302,
            [
                'token_type' => 'bearer',
                'access_token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTl() * 60,
            ]
        );
    }

    private function findOrCreateUser($socialUser)
    {
        if ($authUser = User::where('social_id', $socialUser->id)->first()) {
            return $authUser;
        }

        return User::create([
            'name' => $socialUser->name ?? $socialUser->nickname,
            'email' => $socialUser->email,
            'social_id' => $socialUser->id,
            'email_verified_at' => Carbon::now(),
            'password' => Str::random(20),
        ]);
    }
}
