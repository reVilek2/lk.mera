<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Recommendation;

class RecoveryPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $fromMailer;

    protected $theme = 'mera';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->fromMailer = 'recovery_password';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Сброс Пароля')
            ->markdown('mail.reset_password', [
                'url' => url(config('app.url').route('password.reset', ['token' => $this->token], false)),
                'count' => config('auth.passwords.users.expire')
            ]);
    }
}
