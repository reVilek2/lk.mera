<?php

namespace App\Http\Controllers\Auth;

use App\Models\Token;
use App\Notifications\EmailConfirmNotification;
use App\Rules\PhoneNumber;
use App\Services\PhoneNormalizer;
use App\Services\UserManager;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\Page;
use Hash;
use Mail;
use Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Validation;

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
        Page::setTitle('Sign up | MeraCapital');
        Page::setDescription('Website registration form');

        return view('auth.register');
    }

    public function successRegistrationByEmail(Request $request, $email = null)
    {
        Page::setTitle('Sign up | MeraCapital');
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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        Page::setTitle('Sign up | MeraCapital');
        Page::setDescription('Website registration form');

        /**
         * Правила валидации и пост данные
         */
        $data = $request->all();

        $rules = [
            'password' => 'required:create|between:6,50|confirmed',
            'password_confirmation' => 'required_with:password|between:6,50'
        ];
        if(preg_match('/^\+?[\d]+$/', $request->phone_or_email)) {
            // Регистрация через телефон
            $register_type = User::REGISTER_TYPE_PHONE;
            $phoneNormalizer = new PhoneNormalizer();
            $phoneNormalized = $phoneNormalizer->normalize($request->phone_or_email);
            if ($phoneNormalized) {
                $data['phone_or_email'] = '+'.$phoneNormalized;
            }
            $rules['phone_or_email'] = ['required', new PhoneNumber('Поле phone or email имеет ошибочный формат.')];
        }else{
            // Регистрация через email
            $register_type = User::REGISTER_TYPE_EMAIL;
            $rules['phone_or_email'] = 'required|between:6,255|email';
        }
        /**
         * Валидация данных
         */
        $validation = $this->validateRegisterData($data, $rules, $register_type);
        if (!empty($validation->errors()->messages())) {

            return back()->withErrors($validation)->withInput();
        }
        /**
         * Регистрация нового пользователя
         */
        $user = User::create([
            'email' => $register_type === User::REGISTER_TYPE_EMAIL ? $data['phone_or_email']: null,
            'phone' => $register_type === User::REGISTER_TYPE_PHONE ? $data['phone_or_email']: null,
            'password' => Hash::make($data['password']),
            'api_token' => Token::generate() // API tokens
        ]);

        $user->assignRole('user');

        if ($register_type === User::REGISTER_TYPE_EMAIL) {
            // Если регистрация через email
            // отсылаем на почту письмо
            $this->userManager->sendActivationEmail($user);

            return redirect()->route('successRegistrationByEmail', $user->email);
        } else {
            // Если регистрация через телефон
            // отсылаем sms на телефон
            // ... code
            return redirect()->route('phone.confirm', $user->phone);
        }
    }

    /**
     * @param array $data
     * @param array $rules
     * @param $register_type
     * @return \Illuminate\Validation\Validator
     */
    private function validateRegisterData(array $data, array $rules, $register_type)
    {
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return $validation;
        }

        if ($register_type === User::REGISTER_TYPE_EMAIL && !User::isUniqueEmail($data['phone_or_email'])) {
            $validation->getMessageBag()->add('phone_or_email', 'Данный email уже используется');
        } elseif ($register_type === User::REGISTER_TYPE_PHONE && !User::isUniquePhone($data['phone_or_email'])) {
            $validation->getMessageBag()->add('phone_or_email', 'Данный телефон уже используется');
        }

        return $validation;
    }
}
