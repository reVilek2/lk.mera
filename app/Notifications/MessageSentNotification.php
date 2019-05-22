<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

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
     * @var string
     */
    public $message_html;

    /**
     * Create a new notification instance.
     *
     * @param User $receiver
     * @param User $sender
     * @param Message $message
     * @param string $message_html
     */
    public function __construct(User $receiver, User $sender, Message $message, string $message_html)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->message = $message;
        $this->message_html = $message_html;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','broadcast'];
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
            'message_html' => $this->message_html
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
            'message_html' => $this->message_html
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
        return new PrivateChannel('chat.'.$this->receiver->id);
    }
}
