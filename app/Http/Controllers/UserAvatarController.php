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
use Spatie\MediaLibrary\Models\Media;
use Validator;

class UserAvatarController extends Controller
{
    protected $messages = array(
        'successUpdate' => 'Аватар успешно изменен.',
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
     * @param  User $user_avatar
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request, User $user_avatar)
    {
        $user = $user_avatar;
        if (Auth::user()->id !== $user->id) {
            return redirect()->back()
                ->with('status', 'error')
                ->with('statusMessage', 'Запрещено редактировать чужие данные!');
        }
        $this->validate($request, [
            'avatar'=> 'required|image|mimes:jpeg,png,jpg|max:4000',
        ]);

        $avatar = $request->file('avatar');
        if ($avatar) {
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
        }

        return redirect()
            ->route('profile')
            ->with('status', 'success')
            ->with('statusMessage', $this->messages['successUpdate']);
    }
}
