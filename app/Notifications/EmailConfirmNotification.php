<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmNotification extends Mailable
{
    use Queueable, SerializesModels;

    private $url;

    /**
     * Create a new notification instance.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function build()
    {
        return $this->markdown('mail.email_activation')
            ->with('url', $this->url)
            ->subject('Активация почты');
    }
}
