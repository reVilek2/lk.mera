<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mail;
use Spatie\Permission\Traits\HasRoles;
use Str;

/**
 * App\Models\User
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    const REGISTER_TYPE_EMAIL = 'email';
    const REGISTER_TYPE_PHONE = 'phone';
    const TOKEN_QUERY_NAME = 'token';

    protected $table = 'users';
    protected $guard = 'web';

    /**
     * Validation rules
     */
    public $rules = [
        'email'    => 'nullable|between:6,255|email',
        'phone'    => 'nullable|phone_number',
        'avatar'   => 'nullable|image|max:4000',
        'first_name' => 'nullable|string',
        'second_name' => 'nullable|string',
        'last_name' => 'nullable|string',
        'password' => 'required:create|between:6,50|confirmed',
        'password_confirmation' => 'required_with:password|between:6,50',
    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'second_name',
        'last_name',
        'email',
        'phone',
        'password',
        'password_confirmation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'reset_password_code', 'email_confirmation_code', 'phone_confirmation_code'];

    /**
     * @var array The attributes that aren't mass assignable.
     */
    protected $guarded = ['id', 'reset_password_code', 'email_confirmation_code', 'phone_confirmation_code', 'email_verified_at', 'phone_verified_at', 'activated_at', 'is_activated', 'email_confirmation_code_created_at', 'phone_confirmation_code_created_at', 'reset_password_code_created_at'];

    protected $dates = [
        'email_verified_at',
        'phone_verified_at',
        'created_at',
        'updated_at',
        'activated_at',
        'last_login',
        'reset_password_code_created_at',
        'email_confirmation_code_created_at',
        'phone_confirmation_code_created_at',
    ];

    /**
     * @param $email
     * @return bool
     */
    public static function isUniqueEmail($email)
    {
        $user = self::findByEmail($email);
        return $user ? false : true;
    }

    /**
     * @param $email
     * @return self
     */
    public static function findByEmail($email)
    {
        if (!$email) {
            return;
        }

        return self::where('email', $email)->first();
    }

    /**
     * @param $phone
     * @return bool
     */
    public static function isUniquePhone($phone)
    {
        $user = self::findByPhone($phone);
        return $user ? false : true;
    }

    /**
     * @param $phone
     * @return self
     */
    public static function findByPhone($phone)
    {
        if (!$phone) {
            return;
        }

        return self::where('phone', $phone)->first();
    }

    /**
     * Get an activation code for the given user.
     * @return string
     */
    public function getEmailActivationCode()
    {
        $this->email_confirmation_code = $emailActivationCode = $this->getRandomString();
        $this->email_confirmation_code_created_at = $this->freshTimestamp();
        $this->save();
        return $emailActivationCode;
    }

    /**
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! (is_null($this->email_verified_at) || empty($this->email_verified_at));
    }

    /**
     * @return bool
     */
    public function hasVerifiedPhone()
    {
        return ! (is_null($this->phone_verified_at) || empty($this->phone_verified_at));
    }

    /**
     * @param string $confirmationCode
     * @return bool
     */
    public function checkEmailConfirmationCode($confirmationCode)
    {
        if (!$confirmationCode || !$this->email_confirmation_code) {

            return false;
        }

        return ($confirmationCode == $this->email_confirmation_code);
    }

    /**
     * @param $code
     * @return bool
     * @throws Exception
     */
    public function attemptEmailConfirmation($code)
    {
        if ($this->hasVerifiedEmail()) {
            throw new Exception('Email уже активирован!');
        }

        if ($code == $this->email_confirmation_code) {
            $this->setEmailConfirmation();
            $this->save();
            return true;
        }

        return false;
    }

    public function setEmailConfirmation()
    {
        $this->email_confirmation_code = null;
        $this->email_confirmation_code_created_at = null;
        $this->email_verified_at = $this->freshTimestamp();

        if (is_null($this->activated_at) || empty($this->activated_at)) {
            $this->activated_at = $this->freshTimestamp();
        }
        if (!$this->is_activated) {
            $this->is_activated = true;
        }
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    //
    // Helpers
    //

    /**
     * Generate a random string
     * @param int $length
     * @return string
     */
    public function getRandomString($length = 42)
    {
        return Str::random($length);
    }
}
