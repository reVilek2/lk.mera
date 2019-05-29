<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Message
 *
 * @property int $id
 * @property string $message
 * @property int $is_seen
 * @property int $deleted_from_sender
 * @property int $deleted_from_receiver
 * @property int $user_id
 * @property int $conversation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read mixed $humans_time
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereDeletedFromReceiver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereDeletedFromSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereUserId($value)
 * @mixin \Eloquent
 * @property int $chat_id
 * @property string $type
 * @property-read \App\Models\Chat $chat
 * @property-read mixed $created_at_humanize
 * @property-read \App\Models\User $sender
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MessageStatus[] $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereType($value)
 */
class Message extends Model
{
    protected $table = 'messages';

    public $timestamps = true;

    public $fillable = [
        'message','user_id', 'type'
    ];

    protected $appends = ['created_at_humanize'];
    /*
     * make dynamic attribute for human readable time
     *
     * @return string
     * */
    public function getCreatedAtHumanizeAttribute()
    {
        return humanize_date($this->created_at, 'd F, H:i');
    }

    //Relationships
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function status()
    {
        return $this->hasMany(MessageStatus::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Its an alias of user relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->user();
    }
}
