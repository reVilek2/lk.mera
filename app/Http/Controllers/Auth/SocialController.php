<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Models\Token;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Builder;

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
        list($firstName, $lastName) = explode(' ', $socialUser->name);

        $user = User::where('email', $socialUser->email)->orWhere(function (Builder $query) use ($driver, $socialUser) {
            $query->where('social_type', $driver);
            $query->where('social_id', $socialUser->id);
        })->first();

        if (!$user) {
            $user = User::create([
                'email' => $socialUser->email,
                'api_token' => Token::generate(), // API tokens
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            $user->assignRole('user');
        }

        if (!$user->social_type) {
            $user->social_type = $driver;
            $user->social_id = $socialUser->id;
            $user->save();
        }

        Auth::loginUsingId($user->id);

        if (!$user->email) {
            return redirect()->route('profile', ['#settings']);
        }

        return redirect()->intended($this->redirectTo);
    }
}