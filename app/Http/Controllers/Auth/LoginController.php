<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\Page;
use App\Services\PhoneNormalizer;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/reports';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show User login form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        Page::setTitle('Sign in');
        Page::setDescription('Website authorization form');

        return view('auth.login');
    }

    /**
     * auth
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $rules['password'] = 'required|between:6,50';

        // $phoneNormalizer = new PhoneNormalizer();
        // $phoneNormalized = $phoneNormalizer->normalize($request->login);
        // if ($phoneNormalized) {
        //     $data['login'] = '+'.$phoneNormalized;
        // }
        $rules['login'] = 'required|between:6,50';

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput($request->only('login'));
        }

        $phone = PhoneNormalizer::simple($data['login']);
        $user = User::where('email', $data['login'])->orWhere('phone', $phone)->firstOrFail();

        if ($user && !$user->hasVerifiedPhone()) {
            return redirect()->route('phone.confirm.info', $user->phone);
        }

        $credentials = [
            'phone'    => $user->phone,
            'password' => $data['password']
        ];

        if (Auth::guard('web')->attempt($credentials, true)
        ) {
            $user = Auth::user();
            if($user->is_client){
                return redirect()->intended('chat');
            }
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors(['authentication_failed' => '?????????????????????? ?????????? ?????????? ?????? ????????????!'])->withInput($request->only('login'));
    }

    /**
     * Logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return redirect(route('login'));
    }
}
