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
 * @property int $initiator_user_id
 * @property string $operation
 * @property string|null $meta_data
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereInitiatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Transaction whereOperation($value)
 * @property-read \App\Models\User $user
 */
class Transaction extends Model
{
    const INCOMING = 'incoming';
    const OUTGOING = 'outgoing';

    protected $table = 'billing_transactions';
    protected $fillable = ['status_id','type_id','user_id', 'initiator_user_id','amount','receiver_acc_id','sender_acc_id', 'operation', 'comment', 'meta_data'];
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
    public function getMetaDataAttribute($value)
    {
        return $value ? unserialize($value, array('allowed_classes' => true)) : [];
    }
    public function setMetaDataAttribute($value)
    {
        $this->attributes['meta_data'] = serialize($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(TransactionStatus::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'type_id');
    }

    /**
     * @return \App\Models\User|null
     */
    public function getUser()
    {
        return $this->user()->first();
    }

    public function getStatusCode()
    {
        $status = $this->belongsTo(TransactionStatus::class, 'status_id')->first();
        return $status ? $status->code : null;
    }

    public function setStatus(string $status)
    {
        $transactionSuccessStatus = BillingService::getTransactionStatusByCode($status);
        $this->status()->associate($transactionSuccessStatus);

        return $this;
    }
}
