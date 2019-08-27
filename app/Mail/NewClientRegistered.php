<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class NewClientRegistered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $client)
    {
        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = route('users.show', ['id' => $this->client->id]);
        $client_name = $this->client->getUserName();

        return $this->subject('Новый пользователь подтвержден')
            ->markdown('mail.new_client', ['url' => $url, 'client_name' => $client_name]);
    }
}
