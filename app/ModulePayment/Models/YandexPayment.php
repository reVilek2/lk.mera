<?php
namespace App\ModulePayment\Models;

use App\Models\Transaction;
use App\Models\User;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use Illuminate\Database\Eloquent\Model;
use MoneyAmount;

/**
 * Class YandexPayment
 *
 * @property int $id
 * @property string|null $idempotency_key
 * @property int $amount
 * @property int $paid
 * @property string $payment_id
 * @property string $payment_type
 * @property string|null $payment_method_type
 * @property string|null $payment_meta
 * @property string $status
 * @property string|null $description
 * @property int $user_id
 * @property int $transaction_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $paid_amount
 * @property-read \App\Models\Transaction $transaction
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment wherePaymentMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment wherePaymentMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModulePayment\Models\YandexPayment whereUserId($value)
 * @mixin \Eloquent
 */
class YandexPayment extends Model implements ModelPaymentInterface
{
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'yandex_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idempotency_key' ,
        'amount',
        'paid',
        'payment_id' ,
        'payment_type' ,
        'payment_method_type' ,
        'payment_meta',
        'status',
        'description',
        'user_id',
        'transaction_id',
    ];

    public function getAmountAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = MoneyAmount::toExternal($value);
    }
    public function getPaidAmountAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setPaidAmountAttribute($value)
    {
        $this->attributes['paid_amount'] = MoneyAmount::toExternal($value);
    }
    public function getPaymentMetaAttribute($value)
    {
        return $value ? unserialize($value, array('allowed_classes' => true)) : [];
    }
    public function setPaymentMetaAttribute($value)
    {
        $this->attributes['payment_meta'] = serialize($value);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|null|object
     */
    public function getTransaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id')->first();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if ($status === self::STATUS_PENDING || $status === self::STATUS_SUCCEEDED || $status === self::STATUS_CANCELED) {
            $this->status = $status;
        }
    }
}