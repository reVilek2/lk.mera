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
    public $fillable = [

    ];

//    protected $appends = [
//        'avatar',
//        'name',
//    ];
//
//    function getAvatarAttribute()
//    {
//        if ($this->group) {
//            $user = $this->owner;
//        } else {
//            $user = $this->owner;
//        }
//        return $this->getAvatar('thumb');
//    }
//    function getNameAttribute()
//    {
//        return $this->getUserName();
//    }

//    public function owner(): MorphTo
//    {
//        return $this->morphTo();
//    }

    public function chatsMembers()
    {
        return $this->hasMany(ChatMember::class, 'chat_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chats_members', 'chat_id','user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id');
    }

//    /*
//     * make a relation between message
//     *
//     * return collection
//     * */
//    public function messages()
//    {
//        return $this->hasMany(Message::class, 'conversation_id')
//            ->with('sender');
//    }
//
//    /*
//     * make a relation between first user from conversation
//     *
//     * return collection
//     * */
//    public function userone()
//    {
//        return $this->belongsTo(User::class,  'user_one');
//    }
//
//    /*
//   * make a relation between second user from conversation
//   *
//   * return collection
//   * */
//    public function usertwo()
//    {
//        return $this->belongsTo(User::class,  'user_two');
//    }
}
