<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RecommendationReceiver;
use Illuminate\Notifications\Notifiable;

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
