<?php

namespace App\Notifications;

use App\Models\Chat;
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
     * @var mixed
     */
    public $chat_id;
    /**
     * @var Chat
     */
    private $chat;
    /**
     * @var User
     */
    private $sender;
    /**
     * @var User
     */
    private $receiver;

    /**
     * Create a new notification instance.
     *
     * @param Chat $chat
     * @param Message $message
     * @param User $sender
     * @param User $receiver
     */
    public function __construct(Chat $chat, Message $message, User $sender, User $receiver)
    {
        $this->chat = $chat;
        $this->chat_id = $chat->id;
        $this->message = $message;
        $this->sender = $sender;
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
            'chat' => [
                'id' => $this->chat->id,
                'url' => route('chat', ['#'.$this->chat->id], false)
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
            'chat' => [
                'id' => $this->chat->id,
                'url' => route('chat', ['#'.$this->chat->id], false)
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
     * @return array|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notification.user.'.$this->receiver->id);
    }
}
