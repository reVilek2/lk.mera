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
        Page::setTitle('Профиль | MeraCapital');
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
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return redirect()->back()
                ->with('status', 'error')
                ->with('statusMessage', $this->messages['forbiddenEdit']);
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
        if (Auth::user()->id !== $user->id) {
            return redirect()->back()
                ->with('status', 'error')
                ->with('statusMessage', $this->messages['forbiddenEdit']);
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
            ->with('statusMessage', $this->messages['successPasswordUpdate']);
    }
}