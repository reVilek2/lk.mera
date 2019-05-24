<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\NotificationDbChannel;

class MessageSentNotification extends Notification
{
    use Queueable;

    /**
     * Message details
     *
     * @var Message
     */
    public $message;
    /**
     * @var User
     */
    private $receiver;
    /**
     * @var User
     */
    public $sender;

    /**
     * Create a new notification instance.
     *
     * @param User $receiver
     * @param User $sender
     * @param Message $message
     */
    public function __construct(User $receiver, User $sender, Message $message)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->message = $message;
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
            'receiver' =>[
                'id' => $this->receiver->id,
                'name' => $this->receiver->getUserName(),
                'role' => $this->receiver->getUserRole(),
                'avatar' => $this->receiver->getAvatar('thumb'),
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->getUserName(),
                'role' => $this->sender->getUserRole(),
                'avatar' => $this->sender->getAvatar('thumb'),
            ],
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at,
                'created_at_humanize' => humanize_date($this->message->created_at, 'd F, H:i'),
            ],
        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'receiver' =>[
                'id' => $this->receiver->id,
                'name' => $this->receiver->getUserName(),
                'role' => $this->receiver->getUserRole(),
                'avatar' => $this->receiver->getAvatar('thumb'),
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->getUserName(),
                'role' => $this->sender->getUserRole(),
                'avatar' => $this->sender->getAvatar('thumb'),
            ],
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at,
                'created_at_humanize' => humanize_date($this->message->created_at, 'd F, H:i'),
            ],
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
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notification.'.$this->receiver->id);
    }
}
