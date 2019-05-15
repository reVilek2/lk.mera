<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\AuthManager;
use App\Services\Page;
use App\Services\ValidationMessages;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Validator;


class PhoneController extends Controller
{
    /**
     * @var AuthManager
     */
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }


    public function phoneInformation(Request $request, $phone = null)
    {
        Page::setTitle('Information About Phone Number | MeraCapital');
        Page::setDescription('Information About Phone Number');
        try {
            $user = self::getUserByPhone($phone);
            if (!$user) {
                return view('auth.phone.errors')->withErrors(['Телефон поврежден или не существует в системе!']);
            }
            if ($user->hasVerifiedPhone()) {
                return view('auth.phone.confirmed')->with(['phone' => $phone]);
            }else{
                return view('auth.phone.notConfirmed')->with(['phone' => $phone]);
            }
        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return view('auth.phone.errors')->withErrors([$message]);
        }
    }

    public function phoneConfirmForm(Request $request, $phone = null)
    {
        Page::setTitle('Phone Number Form Confirmation | MeraCapital');
        Page::setDescription('Phone Number Form Confirmation');

        try {
            $user = self::getUserByPhone($phone);
            if (!$user) {
                return view('auth.phone.errors')->withErrors(['Телефон поврежден или не существует в системе!']);
            }
            if ($user->hasVerifiedPhone()) {
                return view('auth.phone.confirmed')->with(['phone' => $user->phone]);
            }
        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return view('auth.phone.errors')->withErrors([$message]);
        }

        return view('auth.phone.confirm')->with([
            'phone' => $phone
        ]);
    }

    public static function getUserByPhone($phone = null)
    {
        $validation = Validator::make(
            ['phone' => $phone],
            ['phone' => ['required', new PhoneNumber('Телефон имеет ошибочный формат.')]],
            ValidationMessages::get()
        );

        if ($validation->fails()) {
            return null;
        }

        return User::findByPhone($phone);
    }
}
