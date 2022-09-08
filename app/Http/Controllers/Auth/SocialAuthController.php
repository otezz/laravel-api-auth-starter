<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialRequest;
use App\Models\User;
use App\Models\UserSocial;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect user to social auth provider
     *
     * @param $provider
     * @return mixed
     */
    public function handleRedirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle callback from social auth provider
     *
     * @param $provider
     * @return mixed
     */
    public function handleCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        return $this->processUser($socialUser, $provider);
    }

    /**
     * Handle social authentication by token
     *
     * @param  SocialRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleToken(SocialRequest $request)
    {
        $validated = $request->validated();

        $socialUser = Socialite::driver($validated['provider'])->userFromToken($validated['token']);

        return $this->processUser($socialUser, $validated['provider']);
    }

    /**
     * Process user authentication
     *
     * @param $socialUser
     * @param $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function processUser($socialUser, $provider): \Illuminate\Http\JsonResponse
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(rand(10000000, 99999999)),
                'email_verified_at' => now(),
            ]);

            event(new Registered($user));
        }

        $userSocial = UserSocial::where('user_id', $user->id)->where('provider', $provider)->first();

        if (! $userSocial) {
            UserSocial::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        if ($user->is_suspended) {
            return response()->json(['message' => 'Your account is suspended, please contact Administrator.'], 401);
        }

        return response()->json(['token' => $user->createToken('api')->plainTextToken]);
    }
}
