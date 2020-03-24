<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class ClientConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $fromMailer;

    protected $theme = 'mera';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $client)
    {
        $this->client = $client;
        $this->fromMailer = 'client_confirmation';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $login = $this->client->phone;

        $code = implode('!', [$this->client->id, $this->client->makeEmailActivationCode()]);
        $url = route('email.confirm', $code);

        return $this->subject('Подтверждение почты')
            ->markdown('mail.client_confirmation', [
                'login' => $login,
                'code' => $code,
                'url' => $url,
            ]);
    }
}
