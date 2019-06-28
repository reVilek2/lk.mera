<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthManager;
use App\Services\Page;

use Illuminate\Http\Request;
use Validator;


class EmailController extends Controller
{
    /**
     * @var AuthManager
     */
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * @param Request $request
     * @param null $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function emailConfirm(Request $request, $token = null)
    {
        Page::setTitle('Activation Email | MeraCapital');
        Page::setDescription('Website email confirmation form');

        try {
            $parsedCode = $this->parseEmailActivationCode($token);
            if (!empty($parsedCode['errors'])) {
                return view('auth.email.errors')->withErrors($parsedCode['errors']);
            }
            /**
             * Активация email
             */
            /** @var User $user */
            $user = $parsedCode['user'];
            $user->attemptEmailConfirmation($parsedCode['parseCode']);

            return redirect()->route('email.confirm.info', $user->email);
        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return view('auth.email.errors')->withErrors([$message]);
        }
    }

    /**
     * @param Request $request
     * @param null $email
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function emailInformation(Request $request, $email = null)
    {
        Page::setTitle('Информация о Email | MeraCapital');
        Page::setDescription('Информация о Email');
        try {
            $user = self::getUserByEmail($email);
            if (!$user) {
                return view('auth.email.errors')->withErrors(['Email поврежден или не существует в системе!']);
            }
            if ($user->hasVerifiedEmail()) {
                return view('auth.email.confirmed')->with(['email' => $email]);
            }else{
                return view('auth.email.notConfirmed')->with(['email' => $email]);
            }
        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return view('auth.email.errors')->withErrors([$message]);
        }
    }

    /**
     * @param $token
     * @return array
     */
    private function parseEmailActivationCode($token)
    {
        $result = [
            'user' => null,
            'parseCode' => null,
            'token' => null,
            'errors' => []
        ];
        $parts = explode('!', $token);
        if (count($parts) != 2) {
            $result['errors'] = ['Неверный код активации email.'];
            return $result;
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !strlen(trim($code)) || !$code) {
            $result['errors'] = ['Неверный код активации email.'];
            return $result;
        }
        /** @var User $user*/
        if (!$user = $this->authManager->findUserById($userId)) {
            $result['errors'] = ['Неверный код активации email.'];
            return $result;
        }

        // проверяем что email еще не активирован
        if ($user->hasVerifiedEmail()) {
            $result['errors'] = ['Email уже активирован!'];
            return $result;
        }

        // проверяем что код совпадает с кодом пользователя
        if (!$user->checkEmailConfirmationCode($code)) {
            $result['errors'] = ['Неверный код активации email.'];
            return $result;
        }

        $result['errors'] = [];
        $result['user'] = $user;
        $result['parseCode'] = $code;
        $result['activationCode'] = $token;

        return $result;
    }

    public static function getUserByEmail($email = null)
    {
        $validation = Validator::make(
            ['email' => $email],
            ['email' => 'required|between:6,255|email']
        );

        if ($validation->fails()) {
            return null;
        }

        return User::findByEmail($email);
    }
}
