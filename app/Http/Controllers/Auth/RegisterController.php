<?php

namespace App\Http\Controllers\Auth;

use App\Models\Token;
use App\Notifications\EmailConfirmNotification;
use App\Rules\PasswordStrength;
use App\Services\UserManager;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Rules\PhoneNumber;
use App\Services\Page;
use App\Services\PhoneNormalizer;
use Hash;
use Mail;
use Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * Create a new controller instance.
     *
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->middleware('guest');
        $this->userManager = $userManager;
    }

    /**
     * Show admin register form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        Page::setTitle('Sign up');
        Page::setDescription('Website registration form');

        return view('auth.register');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function register(Request $request)
    {
        Page::setTitle('Sign up');
        Page::setDescription('Website registration form');

        /**
         * Правила валидации и пост данные
         */
        $data = $request->all();

        $rules = [
            'email' => 'required|between:6,255|email',
            'password' => ['required:create', 'confirmed', new PasswordStrength],
            'password_confirmation' => 'required_with:password',
            'phone' => 'required|regex:/^\+?[0-9\-\(\) ]{4,20}$/'
        ];

        /**
         * Валидация данных
         */
        $validation = $this->validateRegisterData($data, $rules);
        if (!empty($validation->errors()->messages())) {
            return back()->withErrors($validation)->withInput();
        }

        /**
         * Регистрация нового пользователя
         */
        $user = User::create([
            'email' => $data['email'],
            'phone' => PhoneNormalizer::simple($data['phone']),
            'password' => Hash::make($data['password']),
            'api_token' => Token::generate() // API tokens
        ]);

        $user->assignRole('user');

        // Если регистрация через телефон
        // отсылаем sms на телефон
        $this->userManager->sendActivationPhone($user);
        return redirect()->route('phone.confirm', $user->phone);
    }

    /*
     *Depricated
     */
    public function successRegistrationByEmail(Request $request, $email = null)
    {
        Page::setTitle('Sign up');
        Page::setDescription('Website registration form');
        try {
            $user = EmailController::getUserByEmail($email);
            if (!$user) {
                throw new \Exception('Email is corrupted or does not exist');
            }
            if ($user->hasVerifiedEmail()) {
                throw new \Exception('Email is corrupted or does not exist');
            }
        } catch (\Exception $ex) {

            return redirect()->route('login');
        }

        return view('auth.successRegistrationByEmail')->with(['email' => $email]);
    }

    /**
     * @param array $data
     * @param array $rules
     * @param $register_type
     * @return \Illuminate\Validation\Validator
     */
    private function validateRegisterData(array $data, array $rules)
    {
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return $validation;
        }

        if (!User::isUniqueEmail($data['email'])) {
            $validation->getMessageBag()->add('email', 'Данный email уже используется');
        }

        if (!User::isUniquePhone($data['phone'])) {
            $validation->getMessageBag()->add('phone', 'Данный телефон уже используется');
        }

        return $validation;
    }
}
