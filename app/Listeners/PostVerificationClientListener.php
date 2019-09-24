<?php

namespace App\Listeners;

use App\Events\ClientVerified;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use App\Mail\NewClientRegisterd;
use Illuminate\Support\Facades\Mail;
use App\Services\UserManager;
use App\Mail\NewClientRegistered;
use App\Mail\ClientConfirmation;

class PostVerificationClientListener
{
    /**
     * UserController constructor.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Handle the event.
     *
     * @param  ClientVerified  $event
     * @return void
     */
    public function handle(ClientVerified $event)
    {
        $client = $event->client;

        $default_manager = User::find(config('mera-capital.default_manager_id'));
        if(!$default_manager || $default_manager->hasRole('manager')){
            $this->userManager->changeManager($client, $client->getManager(), $default_manager);
        }

        $mail = Mail::to(config('mera-capital.notification_email'));
        $administration_staff = User::role(['admin'])->get();
        if($administration_staff->count()){
            $mail->bcc($administration_staff);
        }
        $mail->send(new NewClientRegistered($client));

        $mail = \MultiMail::to($client);
        $mail->send(new ClientConfirmation($client));
    }
}
