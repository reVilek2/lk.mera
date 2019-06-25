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
        'card_id' => null,
        'year' => null,
        'month' => null,
        'type' => null,
        'pan' => null,
        'first' => null,
        'last' => null,
        'card_default' => false,
    ])
    {
        $card = $this;

        if ($metaData['card_id'] && !empty($metaData['card_id'])) {
            if ($issetCardById = PaymentCard::whereCardId($metaData['card_id'])->first()) { // если существует карта с такимиже уникальным ключом то обновляем ее
                $card = $issetCardById;
            } else { // иначе ищем по данным карты
                $issetCardByData = PaymentCard::where('type', $metaData['type'])->where('first', $metaData['first'])->where('last', $metaData['last'])->first();
                if ($issetCardByData) { // если существует карта с такимиже данными то обновляем ее
                    $card = $issetCardByData;
                }
            }

            $card->card_id = $metaData['card_id'];
            $card->year = $metaData['year'];
            $card->month = $metaData['month'];
            $card->type = $metaData['type'];
            $card->pan = $metaData['pan'];
            $card->first = $metaData['first'];
            $card->last = $metaData['last'];
            $card->card_default = $metaData['card_default'];
            $card->user_id = $user->id;

            $card->save();
        }

        return $card;
    }
}