<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\Page;
use App\Services\UserManager;
use Illuminate\Http\Request;
use Validator;


class PhoneController extends Controller
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
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

            $resend_phone_code_time = $this->userManager->getResendPhoneCodeTime($user);

        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return view('auth.phone.errors')->withErrors(['phone_confirm_form' => $message]);
        }

        return view('auth.phone.confirm')->with([
            'phone' => $phone,
            'resend_phone_code_time' => $resend_phone_code_time
        ]);
    }

    public function phoneConfirm(Request $request, $phone = null)
    {
        $code = $request->input('code');
        $validation = Validator::make(['code' => $code], [
            'code'=> 'required|regex:/^\d+$/',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            $errors = json_decode($errors);
            if($request->ajax()){
                return response()->json([
                    'status'=>'error',
                    'errors' => $errors
                ], 200);
            } else {
                return back()->withErrors($validation)->withInput();
            }
        }
        try {
            $user = self::getUserByPhone($phone);
            if (!$user) {
                if ($request->ajax()){
                    return response()->json([
                        'status'=>'exception',
                        'message' => 'Телефон поврежден или не существует в системе!'
                    ], 200);
                } else {
                    return view('auth.phone.errors')->withErrors(['Телефон поврежден или не существует в системе!']);
                }
            }
            if ($user->hasVerifiedPhone()) {
                if ($request->ajax()){
                    return response()->json([
                        'status'=>'exception',
                        'message' => 'Телефон уже подтвержден.'
                    ], 200);
                } else {
                    return view('auth.phone.confirmed')->with(['phone' => $user->phone]);
                }
            }

            $result = $this->userManager->checkActivationCode($user, $code);
            if ($result->status !== 'success') {
                if ($request->ajax()){
                    return response()->json([
                        'status'=>'error',
                        'errors' => ['code' => [$result->message]]
                    ], 200);
                } else {
                    return back()->withErrors(['code' => $result->message]);
                }
            }

            $user->attemptPhoneConfirmation($code);
            if ($request->ajax()){
                return response()->json([
                    'status'=>'success',
                    'user' => $user->fresh()
                ], 200);
            } else {
                return redirect()->route('phone.confirm.info', $user->phone);
            }

        } catch (\Exception $ex) {

            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            if ($request->ajax()){
                return response()->json([
                    'status'=>'exception',
                    'message' => $message
                ], 200);
            } else {
                return view('auth.phone.errors')->withErrors([$message]);
            }
        }
    }

    public function codeResend(Request $request, $phone = null)
    {
        try {
            $user = self::getUserByPhone($phone);
            if (!$user) {
                return response()->json([
                    'status'=>'exception',
                    'message' => 'Телефон поврежден или не существует в системе!'
                ], 200);
            }
            if ($user->hasVerifiedPhone()) {
                return response()->json([
                    'status'=>'exception',
                    'message' => 'Телефон уже подтвержден.'
                ], 200);
            }

            $resend_phone_code_time = $this->userManager->getResendPhoneCodeTime($user);
            if ($resend_phone_code_time !== 0) {
                return response()->json([
                    'status'=>'success',
                    'resend_phone_code_time' => $resend_phone_code_time
                ], 200);
            }
            $this->userManager->sendActivationPhone($user);
            $resend_phone_code_time = $this->userManager->getResendPhoneCodeTime($user->fresh());
            return response()->json([
                'status'=>'success',
                'resend_phone_code_time' => $resend_phone_code_time
            ], 200);

        } catch (\Exception $ex) {
            $message = 'На сервере произошла ошибка. Пожалуйста, обратитесь к администратору системы.';
            if (config('app.debug')) {
                $message = $ex->getMessage();
            }
            return response()->json([
                'status'=>'exception',
                'message' => $message
            ], 200);
        }
    }

    public function phoneInformation(Request $request, $phone = null)
    {
        Page::setTitle('Информация о телефоне | MeraCapital');
        Page::setDescription('Информация о телефоне');
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

    public static function getUserByPhone($phone = null)
    {
        $validation = Validator::make(
            ['phone' => $phone],
            ['phone' => ['required', new PhoneNumber('Телефон имеет ошибочный формат.')]]
        );

        if ($validation->fails()) {
            return null;
        }

        return User::findByPhone($phone);
    }
}
