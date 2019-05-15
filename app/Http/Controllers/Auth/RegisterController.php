<?php

namespace App\Http\Controllers\Auth;

use App\Models\Token;
use App\Rules\PhoneNumber;
use App\Services\PhoneNormalizer;
use App\Services\ValidationMessages;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\Page;
use Hash;
use Illuminate\Validation\ValidationException;
use Mail;
use Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Validation;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function register(Request $request)
    {
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
            $this->sendActivationEmail($user);
            $message = 'Вы успешно зарегистрировались. На '.$user->email.' выслано письмо для подтверждения. Пожалуйста, проверьте почтовый ящик.';
        } else {
            // Если регистрация через телефон
            // отсылаем sms на телефон
            // ... code
            $message = 'Вы успешно зарегистрировались. На '.$user->phone.' выслано sms для подтверждения. Пожалуйста, проверьте sms сообщения';
        }

        return back()->with('status', 'success')->with('statusMessage', $message);
    }

    /**
     * @param array $data
     * @param array $rules
     * @param $register_type
     * @return \Illuminate\Validation\Validator
     */
    private function validateRegisterData(array $data, array $rules, $register_type)
    {
        $validation = Validator::make($data, $rules, ValidationMessages::get());
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

    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    private function sendActivationEmail(User $user)
    {
        try {
            $code = implode('!', [$user->id, $user->getEmailActivationCode()]);

            $link = $this->makeActivationEmailUrl($code);

            $data = [
                'link' => $link,
                'code' => $code
            ];

            Mail::send('mail.email_activation', $data, function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Email activation');
            });
        } catch (\Exception $ex) {

            //@TODO сделать логирование не успешных отправлений
        }
    }

    /**
     * Returns a link used to activate the user account.
     * @param $code
     * @return string
     */
    private function makeActivationEmailUrl($code)
    {
        return route('email.confirm', $code);
    }
}
