<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\PhoneNormalizer;
use App\Services\UserManager;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class UserChangePasswordController extends Controller
{
    protected $messages = array(
        'successUpdate' => 'Пароль успешно изменен.',
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
     * @param  User $user_password
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request, User $user_password)
    {
        $user = $user_password;
        if (Auth::user()->id !== $user->id) {
            return redirect()->back()
                ->with('status', 'error')
                ->with('statusMessage', 'Запрещено редактировать чужие данные!');
        }
        $old_password = trim($request->old_password);
        if (!$old_password || empty($old_password) || !Hash::check($old_password, $user->password)) {

            return back()->withErrors(['old_password' => 'Текущий пароль указан неверно'])->with('active_tab', 'password');
        }

        $validation = Validator::make(['password' => $request->password, 'password_confirmation' => $request->password_confirmation], [
            'password' => 'required:create|between:6,50|confirmed',
            'password_confirmation' => 'required_with:password|between:6,50'
        ]);
        if ($validation->fails()) {

            return back()->withErrors($validation)->with('active_tab', 'password');
        }

        $user->password = Hash::make(trim($request->password));
        $user->save();

        return redirect()
            ->route('profile')
            ->with('status', 'success')
            ->with('statusMessage', $this->messages['successUpdate']);
    }
}
