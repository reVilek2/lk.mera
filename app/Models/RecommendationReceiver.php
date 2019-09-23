<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RecommendationReceiver;

/**
 * App\Models\RecommendationReceiver
 *
 * @property int $id
 * @property int $recommendation_id
 * @property int $client_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $client
 * @property-read mixed $client_name
 * @property-read \App\Models\RecommendationReceiver $recommendation
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereRecommendationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendationReceiver whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RecommendationReceiver extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    protected $table = 'recommendation_receivers';

    public $fillable = [
        'client_id', 'recommendation_id', 'status'
    ];

    protected $appends = ['client_name'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function recommendation()
    {
        return $this->belongsTo(RecommendationReceiver::class);
    }

    public function getClientNameAttribute() {
        return $this->client->name;
    }
}
