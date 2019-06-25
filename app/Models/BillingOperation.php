<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoneyAmount;


/**
 * App\Models\BillingOperation
 *
 * @property int $id
 * @property int $account_id
 * @property int $transaction_id
 * @property int $type_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BillingOperation extends Model
{
    protected $table = 'billing_operations';
    protected $fillable = ['account_id','transaction_id', 'type_id', 'amount'];
    public $timestamps = true;

    public function getAmountAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = MoneyAmount::toExternal($value);
    }
}
