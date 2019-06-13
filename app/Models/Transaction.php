<?php

namespace App\Models;

use BillingService;
use Illuminate\Database\Eloquent\Model;
use MoneyAmount;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int $user_id
 * @property int $receiver_acc_id
 * @property int $sender_acc_id
 * @property int $amount
 * @property int $status_id
 * @property int $type_id
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TransactionStatus $status
 * @property-read \App\Models\TransactionType $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereReceiverAccId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereSenderAccId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereUserId($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    protected $table = 'billing_transactions';
    protected $fillable = ['status_id','type_id','user_id','amount','receiver_acc_id','sender_acc_id'];
    public $timestamps = true;

    protected $with = ['status','type'];

    public function getAmountAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = MoneyAmount::toExternal($value);
    }

    public function status()
    {
        return $this->belongsTo(TransactionStatus::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'type_id');
    }

    public function setStatus(string $status)
    {
        $transactionSuccessStatus = BillingService::getTransactionStatusByCode($status);
        $this->status()->associate($transactionSuccessStatus);
        $this->save();

        return $this;
    }
}
