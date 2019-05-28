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
 */
class Chat extends Model
{
    protected $table = 'chats';
    public $timestamps = true;
    protected $fillable = ['name'];

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
     * @return Chat
     */
    public function start($users, $name = null, $private = true)
    {
        /** @var Chat $chat */
        $chat = $this->create([
            'name' => !$name && !$private ? 'группа': $name,
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
