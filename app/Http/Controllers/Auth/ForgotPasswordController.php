<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Page;
use App\Services\PhoneNormalizer;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Services\UserManager;
use Auth;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * @var UserManager
     */
    private $userManager;

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        Page::setTitle('Forgot password');
        Page::setDescription('Website forgot password form');

        return view('auth.passwords.forgot');
    }

    /**
     * Send a reset code to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetCode(Request $request)
    {
        $phone = PhoneNormalizer::simple($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return $this->sendResetLinkFailedResponse($request, 'Пользователь не найден');
        }

        $response = $this->userManager->sendResetCode($user);

        return $response
            ? $this->sendResetLinkResponse($request)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    public function code(Request $request)
    {
        $phone = PhoneNormalizer::simple($request->phone);
        User::where('phone', $phone)->firstOrFail();

        return view('auth.passwords.code', ['phone' => $phone]);
    }

    public function checkCode(Request $request)
    {
        $phone = PhoneNormalizer::simple($request->phone);
        $user = User::where('phone', $phone)->firstOrFail();

        if ($user->isResetCodeExpired()) {
            return $this->sendResetLinkFailedResponse($request, 'Время жизни кода истекло');
        }

        if ($user->reset_code != $request->code) {
            return $this->sendResetLinkFailedResponse($request, 'Операция невозможна. Неверный код');
        }

        // сбрасываем пароль
        $user->password = '';
        $user->save();

        Auth::loginUsingId($user->id);

        return redirect()->route('profile', ['#password']);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request)
    {
        $phone = PhoneNormalizer::simple($request->phone);

        return redirect()->route('password.code', ['phone' => $phone]);
    }

    protected function sendResetLinkFailedResponse(Request $request, $msg)
    {
        return back()
                ->withInput($request->only('phone'))
                ->withErrors(['phone' => $msg]);
    }

    public function broker()
    {
        return Password::broker();
    }
}
