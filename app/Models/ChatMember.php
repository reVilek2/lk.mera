<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ChatMember extends Model
{
    protected $table = 'chats_members';
    public $timestamps = true;
    public $fillable = [

    ];

    // Relationships
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class);
    }
}
