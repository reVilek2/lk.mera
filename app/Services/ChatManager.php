<?php
namespace App\Services;


use App\Models\Chat;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class ChatManager
{

    public function getChatsByUser(User $user = null)
    {
        if (!$user) {
            return null;
        }
        $chats = $user->chats()
            ->with(['messages'=> function ($query) {
                $query->orderBy('created_at', 'ASC')->with('status');
            }])
            ->get();
//        dd($chats);
//        $query->whereHas('status', function ($qr) use($user) {
//            $qr->where(
//                function ($q) use ($user) {
//                    $q->where(function ($q2) use ($user) {
//                        $q2->where('user_id', '=', $user->id)
//                            ->where('deleted', 0);
//                    })->orWhere(
//                        function ($q2) use ($user) {
//                            $q2->where('user_id', '!=', $user->id)
//                                ->where('deleted', 0);
//                        }
//                    );
//                }
//            );
//        });

//        $chats = Chat::with(
//            [
//                'chatsMembers' => function ($query) use ($user) {
//                    $query->where(
//                        function ($qr) use ($user) {
//                            $qr->where('member_id', '=', $user->id)
//                                ->where('deleted', 0);
//                        }
//                    );
//                }
//            ]
//        )->where('deleted', 0)
//            ->with(
//                ['messages' => function ($query) {
//                    $query->where(
//                        function ($qr) use ($user) {
//                            $qr->where('member_id', '=', $user->id)
//                                ->where('deleted', 0);
//                        }
//                    );
//                }]
//            )
//            ->with('owner')
//            ->with(['chatsMembers'])
//            ->get();

        return $chats;
    }


    public function getMessagesByUserId($userId, $authUserId)
    {
        return $this->getConversationsByUserId($userId, $authUserId);
    }

    public function getConversationsByUserId($senderId, $authUserId)
    {
        $conversationId = $this->isConversationExists($senderId, $authUserId);

        if ($conversationId) {
            return $this->getMessagesByConversationId($conversationId, $authUserId);
        }

        return false;
    }

    /**
     * fetch all conversation by using coversation id.
     *
     * @param int $conversationId
     * @param $authUserId
     * @return bool|object
     */
    public function getMessagesByConversationId($conversationId, $authUserId)
    {
        $conversations = $this->getMessagesById($conversationId, $authUserId);

        return $this->makeMessageCollection($conversations, $authUserId);
    }

    /**
     * make sure is this conversation exist for this user with currently loggedin user.
     *
     * @param int $userId
     * @param $authUserId
     * @return bool|int
     */
    public function isConversationExists($userId, $authUserId)
    {
        if (empty($userId)) {
            return false;
        }

        $user = $this->getSerializeUser($authUserId, $userId);
        // находим первую переписку где участвуют два пользователя
        return $this->isExistsAmongTwoUsers($user['one'], $user['two']);
    }

    /**
     * make two users as serialize with ascending order.
     *
     * @param int $user1
     * @param int $user2
     *
     * @return array
     */
    protected function getSerializeUser($user1, $user2)
    {
        $user = [];
        $user['one'] = ($user1 < $user2) ? $user1 : $user2;
        $user['two'] = ($user1 < $user2) ? $user2 : $user1;

        return $user;
    }

    /*
     * check this given two users is already make a conversation
     *
     * @param   int $user1
     * @param   int $user2
     * @return  int|bool
     * */
    public function isExistsAmongTwoUsers($user1, $user2)
    {
        $conversation = Conversation::where(
            function ($query) use ($user1, $user2) {
                $query->where(
                    function ($q) use ($user1, $user2) {
                        $q->where('user_one', $user1)
                            ->where('user_two', $user2);
                    }
                )->orWhere(
                        function ($q) use ($user1, $user2) {
                            $q->where('user_one', $user2)
                                ->where('user_two', $user1);
                        }
                    );
            }
        );

        if ($conversation->exists()) {
            return $conversation->first()->id;
        }

        return false;
    }

    /*
         * get all conversations by given conversation id
         *
         * @param   int $conversationId
         * @param   int $userId
         * @param   int $offset
         * @param   int $take
         * @return  collection
         * */
    public function getMessagesById($conversationId, $userId)
    {
        return Conversation::with(
            [
                'messages' => function ($query) use ($userId) {
                    $query->where(
                        function ($qr) use ($userId) {
                            $qr->where('user_id', '=', $userId)
                                ->where('deleted_from_sender', 0);
                        }
                    )->orWhere(
                        function ($q) use ($userId) {
                            $q->where('user_id', '!=', $userId)
                                ->where('deleted_from_receiver', 0);
                        }
                    );
                }
            ]
        )->with(['userone', 'usertwo'])->find($conversationId);
    }

    /*
     * Make new message collections to response with formatted data
     *
     *@param \Talk\Conversations\Conversation $conversations
     *@return object|bool
     */
    protected function makeMessageCollection($conversations, $authUserId)
    {
        if (!$conversations) {
            return false;
        }

        $collection = (object) null;
        if ($conversations->user_one == $authUserId || $conversations->user_two == $authUserId) {
            $withUser = ($conversations->userone->id === $authUserId) ? $conversations->usertwo : $conversations->userone;
            $collection->withUser = $withUser;
            $collection->messages = $conversations->messages;

            return $collection;
        }

        return false;
    }

    /**
     * create a new message by using receiverid.
     *
     * @param int $receiverId
     * @param $senderId
     * @param string $message
     * @return Message|\Illuminate\Database\Eloquent\Model|string
     */
    public function sendMessageByUserId($receiverId, $senderId, $message)
    {
        if ($conversationId = $this->isConversationExists($receiverId, $senderId)) {
            $message = $this->makeMessage($conversationId, $senderId, $message);

            return $message;
        }

        $convId = $this->newConversation($receiverId, $senderId);
        $message = $this->makeMessage($convId, $senderId, $message);

        return $message;
    }

    /**
     * make new conversation the given receiverId with currently loggedin user.
     *
     * @param int $receiverId
     *
     * @param $senderId
     * @return int
     */
    protected function newConversation($receiverId, $senderId)
    {
        $conversationId = $this->isConversationExists($receiverId, $senderId);
        $user = $this->getSerializeUser($senderId, $receiverId);

        if ($conversationId === false) {
            $conversation = Conversation::create([
                'user_one' => $user['one'],
                'user_two' => $user['two'],
                'status' => 1,
            ]);

            if ($conversation) {
                return $conversation->id;
            }
        }

        return $conversationId;
    }

    /**
     * create a new message by using conversationId.
     *
     * @param int $conversationId
     * @param $senderId
     * @param string $message
     *
     * @return Message|\Illuminate\Database\Eloquent\Model|string
     */
    protected function makeMessage($conversationId, $senderId, $message)
    {
        $message = Message::create([
            'message' => $message,
            'conversation_id' => $conversationId,
            'user_id' => $senderId,
            'is_seen' => 0,
        ]);

        $message->conversation->touch();

        return $message;
    }
}