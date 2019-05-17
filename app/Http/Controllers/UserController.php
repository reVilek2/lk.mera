<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\PhoneNormalizer;
use App\Services\UserManager;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected $messages = array(
        'successUpdate' => 'Пользователь успешно изменен.',
    );

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * UserController constructor.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  User $user
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return redirect()->back()
                ->with('status', 'error')
                ->with('statusMessage', 'Запрещено редактировать чужие данные!');
        }
        $this->validate($request, [
            'email'    => 'nullable|between:6,255|email',
            'phone'    => ['nullable', new PhoneNumber('Поле phone имеет ошибочный формат.')],
            'first_name' => 'nullable|string',
            'second_name' => 'nullable|string',
            'last_name' => 'nullable|string',
        ]);
        $email_changed = false;
        $phone_changed = false;

        $user->first_name = $request->first_name;
        $user->second_name = $request->second_name;
        $user->last_name = $request->last_name;

        if ($request->has('email') && $request->email) {
            if ($user->email !== trim($request->email)) {
                $user->email = trim($request->email);
                $user->email_verified_at = null;
                $email_changed = true;
            }
        }
        if ($request->has('phone') && $request->phone) {
            $phoneNormalizer = new PhoneNormalizer();
            $phoneNormalized = $phoneNormalizer->normalize($request->phone);
            if ($phoneNormalized) {
                $phoneNormalized = '+'.$phoneNormalized;
            }
            if ($user->phone !== $phoneNormalized) {
                $user->phone = $request->phone;
                $user->phone_verified_at = null;
                $phone_changed = true;
            }
        }
        $user->save();

        if ($email_changed) {
            // если изменился email то
            // отсылаем на почту письмо для подтверждения
            $this->userManager->sendActivationEmail($user);
        }

        return redirect()
            ->route('profile')
            ->with('status', 'success')
            ->with('statusMessage', $this->messages['successUpdate'])
            ->with('email_changed', $email_changed)
            ->with('phone_changed', $phone_changed);
    }
}
