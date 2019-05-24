<?php

namespace App\Events;

use App\Models\User;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
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
    public $chat_id;


    /**
     * Create a new event instance.
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
        $this->chat_id = 'chat-'.$sender->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.user.'.$this->receiver->id);
    }
}
