<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RecommendationReceiver;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Recommendation
 *
 * @property int $id
 * @property int $manager_id
 * @property string $title
 * @property string $text
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $text_nl2br
 * @property-read \App\Models\User $manager
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecommendationReceiver[] $receivers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Recommendation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $notifications_count
 * @property-read int|null $receivers_count
 */
class Recommendation extends Model
{
    use Notifiable;

    protected $table = 'recommendations';

    public $fillable = [
        'manager_id', 'title', 'text', 'email'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function receivers()
    {
        return $this->hasMany(RecommendationReceiver::class);
    }

    public function getReceiverByClientId($client_id){
        return $this->receivers()->where('client_id', $client_id)->first();
    }

}
