<?php
namespace App\Services;


use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageStatus;
use App\Models\User;

class ChatManager
{
    /**
     * @var Chat
     */
    private $chat;
    /**
     * @var Message
     */
    private $message;

    public function __construct(Chat $chat, Message $message)
    {
        $this->chat = $chat;
        $this->message = $message;
    }

    /**
     * Creates a new conversation.
     *
     * @param array $participants
     * @param array $data
     *
     * @return Chat
     */
    public function createChat(array $participants, array $data = [])
    {
        return $this->chat->start($participants, $data);
    }

    /**
     * Get Chat list with messages
     *
     * @param User $user
     * @param null $isPrivate
     * @return mixed
     */
    public function getChatList(User $user, $isPrivate = null)
    {
        return $this->chat->getChatList($user, $isPrivate);
    }

    /**
     * Get Chat by id
     * @param $id
     * @return mixed
     */
    public function getChatById($id)
    {
        return $this->chat->getById($id);
    }


    /**
     * Create new message
     *
     * @param $message
     * @param Chat $chat
     * @param User $user
     * @param string $type
     * @return Message|\Illuminate\Database\Eloquent\Model
     */
    public function makeMessage($message, Chat $chat, User $user, $type = 'text')
    {
        //return $this->message->send($message, $chat, $user, $type);
        /** @var Message $message */
        $message = $chat->messages()->create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => $type,
        ]);

        $message->load('sender');
        MessageStatus::make($message, $chat);

        return $message;
    }
}