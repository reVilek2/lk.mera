<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Chat
 *
 * @property int $id
 * @property int $private
 * @property array|null $data
 * @property int $deleted
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Message $last_message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Message[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MessageStatus[] $unReadMessages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereName($value)
 * @property int $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Chat whereClient($value)
 * @property-read int|null $messages_count
 * @property-read int|null $un_read_messages_count
 * @property-read int|null $users_count
 */
class Chat extends Model
{
    protected $table = 'chats';
    public $timestamps = true;
    protected $fillable = ['name', 'client'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chats_users', 'chat_id','user_id')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id')->with('sender');
    }

    /**
     * Return the recent message in a Chat.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function last_message()
    {
        return $this->hasOne(Message::class)->orderBy('messages.id', 'desc')->with('sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unReadMessages()
    {
        return $this->hasMany(MessageStatus::class, 'chat_id')->where('read_at', '=', null);
    }

    /**
     * Starts a new Chat.
     *
     * @param array $users members
     *
     * @param null $name
     * @param bool $private
     * @param bool $client
     * @return Chat
     */
    public function start($users, $name = null, $private = true, $client = false)
    {
        /** @var Chat $chat */
        $chat = $this->create([
            'name' => !$name && !$private ? 'группа': $name,
            'private' => $private,
            'client' => $client,
        ]);

        if ($users) {
            $chat->addUsers($users);
        }

        return $chat;
    }

    /**
     * Add user to chat.
     *
     * @param $userIds
     * @return Chat
     */
    public function addUsers($userIds)
    {
        if (is_array($userIds)) {
            foreach ($userIds as $id) {
                $this->users()->attach($id);
            }
        } else {
            $this->users()->attach($userIds);
        }

        if ($this->fresh()->users->count() > 2) {
            $this->private = false;
            $this->save();
        }

        return $this;
    }

    public function syncUsers($userIds)
    {
        if (!empty($userIds)) {
            $this->users()->detach();

            return $this->addUsers($userIds);
        }

        return $this;
    }

    public function getById($id)
    {
        return $this->whereId($id)->get()->first();
    }

    /**
     * @param $user
     * @param null $isPrivate
     * @return mixed
     */
    public function getChatList($user, $isPrivate = null)
    {
        $chats = $this->join('chats_users', 'chats_users.chat_id', '=', 'chats.id')
            ->with([
                'messages' => function ($query) use ($user) {
                    $query->join('messages_status', 'messages_status.message_id', '=', 'messages.id')
                        ->select('messages_status.*', 'messages.*')
                        ->where('messages_status.user_id', $user->getKey())
                        ->where('messages_status.deleted', 0);
                },
            ])
            ->with(['unReadMessages' => function ($query) use ($user) {
                $query->where('messages_status.user_id', $user->getKey())
                    ->where('messages_status.deleted', 0);
            }])
            ->with([
                'last_message' => function ($query) use ($user) {
                    $query->join('messages_status', 'messages_status.message_id', '=', 'messages.id')
                        ->select('messages_status.*', 'messages.*')
                        ->where('messages_status.user_id', $user->getKey())
                        ->where('messages_status.deleted', 0);
                },
            ])
            ->with('users')
            ->where('chats_users.user_id', $user->getKey());

        if (!is_null($isPrivate)) {
            $chats = $chats->where('chats.private', $isPrivate);
        }
        return $chats->orderBy('chats.updated_at', 'DESC')
            ->orderBy('chats.id', 'DESC')
            ->distinct('chats.id')->get();
    }

    /**
     * Set Messages status as read
     *
     * @param $user
     * @return mixed
     */
    public function markChatAsRead($user)
    {
        return $this->messagesStatus($user, true);
    }

    /**
     * Get Messages status
     * @param $user
     * @return mixed
     */
    public function getMessagesStatus($user)
    {
        return $this->messagesStatus($user, false);
    }

    /**
     * @param $userOne
     * @param $userTwo
     * @return \App\Models\Chat|\Illuminate\Database\Query\Builder|null|object
     */
    public function getPrivateChatBetweenUsers($userOne, $userTwo)
    {
        $userOneId = is_object($userOne) ? $userOne->getKey() : $userOne;
        $userTwoId = is_object($userTwo) ? $userTwo->getKey() : $userTwo;

        return $this->join('chats_users as cu1', function ($join) use ($userOneId) {
                $join->on('cu1.chat_id', '=', 'chats.id')
                    ->where('cu1.user_id', '=', $userOneId);
            })
            ->join('chats_users as cu2', function ($join2) use ($userTwoId) {
                $join2->on('cu2.chat_id', '=', 'chats.id')
                    ->where('cu2.user_id', '=', $userTwoId);
            })
            ->where('private', true)->first();
    }

    /**
     * @param $user
     * @return \App\Models\Chat|\Illuminate\Database\Query\Builder|null|object
     */
    public function getPrivateClientChat($user)
    {
        $userId = is_object($user) ? $user->getKey() : $user;
        return $this->join('chats_users', 'chats_users.chat_id', '=', 'chats.id')
            ->where('chats_users.user_id', $userId)
            ->where('client', true)
            ->where('private', true)->first();
    }

    /**
     * @param $user
     * @param $readAll
     * @return mixed
     */
    private function messagesStatus($user, $readAll)
    {
        $messagesStatus = MessageStatus::where('user_id', $user->getKey())
            ->where('chat_id', $this->id);

        if ($readAll) {
            return $messagesStatus->update(['read_at' => $this->freshTimestamp()]);
        }

        return $messagesStatus->get();
    }
}
