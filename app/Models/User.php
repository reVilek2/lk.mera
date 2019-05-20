<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Exception;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
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
 * @property int $id
 * @property string|null $first_name
 * @property string|null $second_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property int $is_activated
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string|null $email_confirmation_code
 * @property string|null $phone_confirmation_code
 * @property \Illuminate\Support\Carbon|null $email_confirmation_code_created_at
 * @property \Illuminate\Support\Carbon|null $phone_confirmation_code_created_at
 * @property string|null $api_token
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmailConfirmationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmailConfirmationCodeCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsActivated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhoneConfirmationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhoneConfirmationCodeCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSecondName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 */
class User extends Authenticatable implements HasMedia
{
    use HasRoles;
    use Notifiable;
    use HasMediaTrait;

    const AVATAR_COLLECTION_NAME = 'avatar';

    const REGISTER_TYPE_EMAIL = 'email';
    const REGISTER_TYPE_PHONE = 'phone';
    const TOKEN_QUERY_NAME = 'token';

    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLES_USER = 'user';
    const ROLES_USER_CLIENT = 'client';

    const TRANSLATE_ROLES = [
        // base roles
        self::ROLE_ADMIN => 'Администратор',
        self::ROLE_MANAGER => 'Менеджер',
        self::ROLES_USER => 'Пользователь',
        // second only user roles
        self::ROLES_USER_CLIENT => 'Клиент',
    ];

    protected $table = 'users';
    protected $guard = 'web';

    /**
     * Validation rules
     */
    public $rules = [
        'email'    => 'nullable|between:6,255|email',
        'phone'    => 'nullable|phone_number',
        'avatar'   => 'nullable|image|mimes:jpeg,png,jpg|max:4000',
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

    /**
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 45, 45);
        $this->addMediaConversion('small')
            ->fit(Manipulations::FIT_CROP, 128, 128);
        $this->addMediaConversion('medium')
            ->fit(Manipulations::FIT_CROP, 160, 160);
        $this->addMediaConversion('big')
            ->fit(Manipulations::FIT_CROP, 200, 200);
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

    /**
     * User Name
     * @return mixed|null|string
     */
    public function getUserName()
    {
        $name = null;
        if ($this->first_name || $this->last_name) {
            $name = trim($this->first_name.' '.$this->last_name);
        }
        if (!$name ) {
            $name = $this->email_verified_at ? $this->email : null;
        }
        if (!$name ) {
            $name = $this->phone_verified_at ? $this->phone : null;
        }

        return $name ?? 'Пользователь';
    }

    /**
     * Name User Role
     * @return mixed
     */
    public function getUserRole()
    {
        if ( $this->hasRole(self::ROLE_ADMIN) ) {

            return self::TRANSLATE_ROLES[self::ROLE_ADMIN];
        }
        if ( $this->hasRole(self::ROLE_MANAGER) ) {

            return self::TRANSLATE_ROLES[self::ROLE_MANAGER];
        }

        //@TODO разбить роль пользователя на Пользователь/Клиент
        return self::TRANSLATE_ROLES[self::ROLES_USER];
    }

    /**
     * User Avatar
     * @param string $avatar_type
     * @return string
     */
    public function getAvatar($avatar_type = '')
    {
        switch ($avatar_type)
        {
            case 'thumb':
                try {
                    $url = $this->getFirstMedia(self::AVATAR_COLLECTION_NAME) ? $this->getFirstMedia(self::AVATAR_COLLECTION_NAME)->getUrl('thumb') : '';
                }catch (\Exception $t) {
                    $url = '';
                }

                return !empty(trim($url)) ? trim($url) : '/images/profile/avatar_thumb.jpg';
            case 'small':
                try {
                    $url = $this->getFirstMedia(self::AVATAR_COLLECTION_NAME) ? $this->getFirstMedia(self::AVATAR_COLLECTION_NAME)->getUrl('small') : '';
                }catch (\Exception $t) {
                    $url = '';
                }

                return !empty(trim($url)) ? trim($url) :'/images/profile/avatar_small.jpg';
            case 'medium':
                try {
                    $url = $this->getFirstMedia(self::AVATAR_COLLECTION_NAME) ? $this->getFirstMedia(self::AVATAR_COLLECTION_NAME)->getUrl('medium') : '';
                }catch (\Exception $t) {
                    $url = '';
                }

                return !empty(trim($url)) ? trim($url) :'/images/profile/avatar_medium.jpg';
            case 'big':
                try {
                    $url = $this->getFirstMedia(self::AVATAR_COLLECTION_NAME) ? $this->getFirstMedia(self::AVATAR_COLLECTION_NAME)->getUrl('big') : '';
                }catch (\Exception $t) {
                    $url = '';
                }

                return !empty(trim($url)) ? trim($url) :'/images/profile/avatar_big.jpg';
            default:
                try {
                    $url = $this->getFirstMedia(self::AVATAR_COLLECTION_NAME) ? $this->getFirstMedia(self::AVATAR_COLLECTION_NAME)->getUrl() : '';
                }catch (\Exception $t) {
                    $url = '';
                }

                return !empty(trim($url)) ? trim($url) : '/images/profile/avatar.jpg';
        }
    }
}
