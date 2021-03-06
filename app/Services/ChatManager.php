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
     * @param null $name
     * @param bool $private
     * @return Chat
     */
    public function createChat(array $participants, $name = null, $private = true, $client = false)
    {
        return $this->chat->start($participants, $name, $private, $client);
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

    /**
     * Mark all messages in Conversation as read.
     *
     * @param Chat $chat
     * @param $user
     * @return void
     */
    public function markChatAsRead(Chat $chat, $user)
    {
        $chat->markChatAsRead($user);
    }

    /**
     * Gets private chat for a specific user.
     *
     * @param \App\Models\User | int $userOne
     * @param \App\Models\User | int $userTwo
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object
     */
    public function getPrivateChatBetweenUsers($userOne, $userTwo)
    {
        return $this->chat->getPrivateChatBetweenUsers($userOne, $userTwo);
    }

    /**
     * Gets client private chat for a specific user.
     *
     * @param \App\Models\User | int $user
     *
     * @return \App\Models\Chat|\Illuminate\Database\Query\Builder|object
     */
    public function getPrivateClientChat($user)
    {
        return $this->chat->getPrivateClientChat($user);
    }

    /**
     * @param \App\Models\Chat $chat
     * @param array $userIds
     */
    public function chatSyncUsers($chat, array $userIds)
    {
        $chat->syncUsers($userIds);
    }
}