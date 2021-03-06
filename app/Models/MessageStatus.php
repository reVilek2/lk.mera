<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\MessageStatus
 *
 * @property int $id
 * @property int $message_id
 * @property int $chat_id
 * @property int $user_id
 * @property int $deleted
 * @property string|null $read_at
 * @property int $is_sender
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereIsSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MessageStatus whereUserId($value)
 * @mixin \Eloquent
 */
class MessageStatus extends Model
{
    protected $table = 'messages_status';


    public $fillable = [
        'user_id', 'message_id', 'chat_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Creates a new Message Status.
     *
     * @param Message $message
     * @param Chat $chat
     */
    public static function make(Message $message, Chat $chat)
    {
        self::createCustomNotifications($message, $chat);
    }

    public static function createCustomNotifications($message, $chat)
    {
        $notification = [];

        foreach ($chat->users as $user) {
            $is_sender = ($message->user_id == $user->getKey()) ? 1 : 0;

            $notification[] = [
                'user_id' => $user->getKey(),
                'message_id' => $message->id,
                'chat_id' => $chat->id,
                'read_at' => $is_sender ? $message->created_at : null,
                'is_sender' => $is_sender,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
            ];
        }

        self::insert($notification);
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }
}
