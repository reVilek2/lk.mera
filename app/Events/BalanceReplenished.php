<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BalanceReplenished implements ShouldBroadcast
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
     * @var User
     */
    private $receiver;

    /**
     * Create a new event instance.
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
     * @return array|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.user.'.$this->receiver->id);
    }
}
