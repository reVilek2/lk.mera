<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\EmailConfirmNotification;
use Mail;

class UserManager
{
    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    public function sendActivationEmail(User $user)
    {
        try {
            $code = implode('!', [$user->id, $user->getEmailActivationCode()]);

            $url = route('email.confirm', $code);

            Mail::to($user->email)->send(new EmailConfirmNotification($url));

        } catch (\Exception $ex) {
            \Log::error('Ошибка при отправке Email для подтверждения почты: '.$ex->getMessage());
        }
    }
}