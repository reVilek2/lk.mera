<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

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

    protected $table = 'users';
    protected $guard = 'web';

    /**
     * Validation rules
     */
    public $rules = [
        'email'    => 'nullable|between:6,255|email|unique:users',
        'phone'    => 'nullable|phone_number|unique:users',
        'avatar'   => 'nullable|image|max:4000',
        'first_name' => 'nullable|string',
        'second_name' => 'nullable|string',
        'last_name' => 'nullable|string',
        'password' => 'required:create|between:4,255|confirmed',
        'password_confirmation' => 'required_with:password|between:4,255',
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
}
