<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Models\Token;
use App\Models\User;
use Auth;

class SocialController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/reports';

    public function facebookLogin()
    {
        return Socialite::driver('facebook')->redirect();
    }
    public function googleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function facebookCallback()
    {
        return $this->socialLogin('facebook');
    }

    public function googleCallback()
    {
        return $this->socialLogin('google');
    }

    private function socialLogin($driver)
    {
        $socialUser = Socialite::driver($driver)->user();
        $user = User::where('email', $socialUser->email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $socialUser->email,
                'api_token' => Token::generate() // API tokens
            ]);
            $user->assignRole('user');
        }

        Auth::loginUsingId($user->id);

        return redirect()->intended($this->redirectTo);
    }
}