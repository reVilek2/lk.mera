<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ServiceTextNotification extends Notification
{
    use Queueable;

    /**
     * Message details
     *
     * @var array
     */
    public $message;
    /**
     * @var User
     */
    private $receiver;

    /**
     * Create a new notification instance.
     *
     * @param User $receiver
     * @param array $message
     */
    public function __construct(User $receiver, array $message = [ 'text'=> 'сообщение', 'created_at' => '2019-06-27'])
    {
        $this->message = $message;
        $this->receiver = $receiver;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [NotificationDbChannel::class,'broadcast'];
    }



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return[
            'message' => $this->message,
        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->message,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * @return array|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('service.notification.user.'.$this->receiver->id);
    }
}
