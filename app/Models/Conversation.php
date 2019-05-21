<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Conversation
 *
 * @property int $id
 * @property int $user_one
 * @property int $user_two
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Message[] $messages
 * @property-read \App\Models\User $userone
 * @property-read \App\Models\User $usertwo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereUserOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereUserTwo($value)
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    protected $table = 'conversations';
    public $timestamps = true;
    public $fillable = [
        'user_one',
        'user_two',
        'status',
    ];

    /*
     * make a relation between message
     *
     * return collection
     * */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id')
            ->with('sender');
    }

    /*
     * make a relation between first user from conversation
     *
     * return collection
     * */
    public function userone()
    {
        return $this->belongsTo(User::class,  'user_one');
    }

    /*
   * make a relation between second user from conversation
   *
   * return collection
   * */
    public function usertwo()
    {
        return $this->belongsTo(User::class,  'user_two');
    }
}
