<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RecommendationReceiver;

class RecommendationReceiver extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    protected $table = 'recommendation_receivers';

    public $fillable = [
        'client_id', 'recommendation_id', 'status'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function recommendation()
    {
        return $this->belongsTo(RecommendationReceiver::class);
    }
}
