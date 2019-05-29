<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * Create a new event instance.
     *
     * @param Chat $chat
     * @param Message $message
     * @param User $sender
     */
    public function __construct(Chat $chat, Message $message, User $sender)
    {
        $this->chat = $chat;
        $this->chat_id = $chat->id;
        $this->message = $message;
        $this->sender = $sender;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        $users = $this->chat->users()->get();
        foreach ($users as $user) {
            if ((int) $user->id !== (int) $this->sender->id) {
                array_push($channels, new PrivateChannel('chat.user.' . $user->id));
            }
        }

        return $channels;
    }
}
