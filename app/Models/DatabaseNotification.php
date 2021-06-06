<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

/**
 * App\Models\DatabaseNotification
 *
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property int $sender_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $notifiable
 * @property-read \App\Models\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DatabaseNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Notifications\DatabaseNotificationCollection|static[] all($columns = ['*'])
 * @method static \Illuminate\Notifications\DatabaseNotificationCollection|static[] get($columns = ['*'])
 */
class DatabaseNotification extends BaseDatabaseNotification
{
    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable()
    {
        return $this->morphTo();
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
