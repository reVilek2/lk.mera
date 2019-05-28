<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Chat extends Model
{
    protected $table = 'chats';
    public $timestamps = true;
    protected $fillable = ['data'];
    protected $casts = [
        'data' => 'array',
    ];


    public function chatsMembers()
    {
        return $this->hasMany(ChatMember::class, 'chat_id');
    }

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
     * @param array $data
     * @param bool $private
     * @return Chat
     */
    public function start($users, $data = [], $private = true)
    {
        /** @var Chat $chat */
        $chat = $this->create([
            'data' => $data,
            'private' => $private,
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
            ->with('users')
            ->where('chats_users.user_id', $user->getKey());

        if (!is_null($isPrivate)) {
            $chats = $chats->where('chats.private', $isPrivate);
        }

        return $chats->orderBy('chats.updated_at', 'DESC')
            ->orderBy('chats.id', 'DESC')
            ->distinct('chats.id')->get();
    }
}
