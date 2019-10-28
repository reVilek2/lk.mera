<?php
namespace App\ModulePayment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\ModulePayment\Models\PaymentCard
 *
 * @property int $id
 * @property string $card_id
 * @property string|null $year
 * @property string|null $month
 * @property string|null $type
 * @property string|null $first
 * @property string|null $last
 * @property string|null $pan
 * @property int $card_default
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereCardDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard wherePan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\PaymentCard whereYear($value)
 * @mixin \Eloquent
 */
class PaymentCard extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source',
        'card_id' ,
        'year',
        'month',
        'type' ,
        'pan' ,
        'first' ,
        'last' ,
        'card_default' ,
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saveCard(User $user, $metaData = [
        'source' => null,
        'card_id' => null,
        'year' => null,
        'month' => null,
        'type' => null,
        'pan' => null,
        'first' => null,
        'last' => null,
    ])
    {
        $card = $this;
        $cardDefault = $user->getPaymentCardDefault($metaData['source']);

        if ($metaData['card_id'] && !empty($metaData['card_id'])) {
            if ($issetCardById = PaymentCard::getCardForPaymentSystem($metaData['card_id'], $metaData['source'])) { // если существует карта с такимиже уникальным ключом то обновляем ее
                $card = $issetCardById;
            }

            $card->source = $metaData['source'];
            $card->card_id = $metaData['card_id'];
            $card->year = $metaData['year'];
            $card->month = $metaData['month'];
            $card->type = isset($metaData['type']) ? $metaData['type'] : null;
            $card->pan = $metaData['pan'];
            $card->first = isset($metaData['first']) ? $metaData['first'] : null;
            $card->last = isset($metaData['last']) ? $metaData['last'] : null;
            $card->user_id = $user->id;

            if (!$cardDefault) {
                $card->card_default = true;
            } else {
                $card->card_default = $cardDefault->id === $card->id;
            }

            $card->save();
        }

        return $card;
    }

    public static function getCardForPaymentSystem($card_id, $source){
        return PaymentCard::whereCardId($card_id)
            ->whereSource($source)
            ->first();
    }
}
