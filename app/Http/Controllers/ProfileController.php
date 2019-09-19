<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\Page;
use App\Services\PhoneNormalizer;
use App\Services\UserManager;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ProfileController extends Controller
{
    protected $messages = array(
        'forbiddenEdit' => 'Запрещено редактировать чужие данные!',
        'successUpdate' => 'Пользователь успешно изменен.',
        'successAvatarUpdate' => 'Аватар успешно изменен.',
        'successPasswordUpdate' => 'Пароль успешно изменен.',
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
     * Display a home admin page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Page::setTitle('Профиль');
        Page::setDescription('Страница профиля');

        $user = Auth::user();
        $currentManager = $user->getManager();

        return view('profile.index',[
            'user' => Auth::user(),
            'currentManager' => $currentManager,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin') && $currUser->id !== $user->id) {
            abort(403);
        }
        $validation = Validator::make($request->all(), [
            'email'    => 'nullable|between:6,255|email',
            'phone'    => ['nullable', new PhoneNumber('Поле phone имеет ошибочный формат.')],
            'first_name' => 'nullable|string',
            'second_name' => 'nullable|string',
            'last_name' => 'nullable|string',
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            $email_changed = false;
            $phone_changed = false;
            $newEmail = null;
            $newPhone = null;

            if ($request->has('email') && $request->email) {
                $newEmail = trim($request->email);
                if ($user->email !== $newEmail && !User::isUniqueEmail($newEmail)) {
                    return response()->json([
                        'status'=>'error',
                        'errors' => ['email' => ['Данный email уже используется']]
                    ], 200);
                }
            }
            if ($request->has('phone') && $request->phone) {
                $phoneNormalizer = new PhoneNormalizer();
                $newPhone = $phoneNormalizer->normalize(trim($request->phone));
                if ($newPhone) {
                    $newPhone = '+' . $newPhone;
                }
                if ($user->phone !== $newPhone && !User::isUniquePhone($newPhone)) {
                    return response()->json([
                        'status'=>'error',
                        'errors' => ['phone' => ['Данный телефон уже используется']]
                    ], 200);
                }
            }

            if (!$newEmail && !$newPhone) {
                return response()->json([
                    'status'=>'exception',
                    'message' => 'В системе обязательно должен быть email или телефон, иначе аккаунтом нельзя будет пользоваться.'
                ], 200);
            }

            $user->first_name = $request->has('first_name') ? $request->first_name : null;
            $user->second_name = $request->has('second_name') ? $request->second_name : null;
            $user->last_name = $request->has('last_name') ? $request->last_name : null;

            if ($user->email !== $newEmail) {
                $user->email = $newEmail;
                $user->email_verified_at = null;
                $user->email_confirmation_code = null;
                $user->email_confirmation_code_created_at = null;
                $email_changed = $newEmail ? true : false;
            }

            if ($user->phone !== $newPhone) {
                $user->phone = $newPhone;
                $user->phone_verified_at = null;
                $user->phone_confirmation_code = null;
                $user->phone_confirmation_code_created_at = null;
                $phone_changed = $newPhone ? true : false;
            }

            $user->save();

            return response()->json([
                'status'=>'success',
                'user' => $user->fresh(),
                'phone_changed' => $phone_changed,
                'email_changed' => $email_changed,
            ], 200);

        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  User $user
     * @return Response
     * @throws \Exception
     */
    public function updateAvatar(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin') && $currUser->id !== $user->id) {
            return response()->json([
                'status'=>'error',
                'errors' => ['avatar' => [$this->messages['forbiddenEdit']]]
            ], 200);
        }

        $validation = Validator::make(['avatar' => $request->avatar], [
            'avatar'=> 'required|image|mimes:jpeg,png,jpg|max:4000',
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            $avatar = $request->file('avatar');
            // removed old
            $old_avatars = $user->getMedia(User::AVATAR_COLLECTION_NAME);
            if ($old_avatars->count()) {
                foreach ($old_avatars as $old_avatar) {
                    $old_avatar->delete();
                }
            }

            // add new avatar
            $user->addMedia($avatar)
                ->usingName($avatar->getClientOriginalName())
                ->toMediaCollection(User::AVATAR_COLLECTION_NAME);

            return response()->json([
                'status'=>'success',
                'user' => $user->fresh()
            ], 200);

        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  User $user
     * @return Response
     * @throws \Exception
     */
    public function updatePassword(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin') && $currUser->id !== $user->id) {
            abort(403);
        }

        $new_password = trim($request->password);
        $confirm_new_password = trim($request->password_confirmation);

        $old_password = trim($request->old_password);
        if (!$currUser->hasRole('admin')) { // если админ то без старого пароля
            if (!$old_password || empty($old_password) || !Hash::check($old_password, $user->password)) {

                return response()->json([
                    'status' => 'error',
                    'errors' => ['old_password' => ['Текущий пароль указан неверно']]
                ], 200);
            }
        }

        $validation = Validator::make(['password' => $new_password, 'password_confirmation' => $confirm_new_password], [
            'password' => 'required:create|between:6,50|confirmed',
            'password_confirmation' => 'required_with:password|between:6,50'
        ]);
        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {

            $user->password = Hash::make(trim($new_password));
            $user->save();

            return response()->json([
                'status'=>'success',
                'user' => $user->fresh()
            ], 200);

        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * Быстрое подтверждение телефона
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function fastConfirmPhone(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }
        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'status'=>'exception',
                'message' => 'Телефон уже подтвержден!'
            ], 200);
        }
        $user->setPhoneConfirmation();
        $user->save();

        return response()->json([
            'status'=>'success',
            'user' => $user->fresh()
        ], 200);
    }

    /**
     * Быстрое подтверждение почты
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function fastConfirmEmail(Request $request, User $user)
    {
        $currUser = Auth::user();
        if (!$currUser->hasRole('admin')) {
            abort(403);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status'=>'exception',
                'message' => 'Email уже подтвержден!'
            ], 200);
        }
        $user->setEmailConfirmation();
        $user->save();

        return response()->json([
            'status'=>'success',
            'user' => $user->fresh()
        ], 200);
    }
}
