<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoneyAmount;

/**
 * App\Models\BillingAccount
 *
 * @property int $id
 * @property int $user_id
 * @property int $acc_type_id
 * @property int $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereAccTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccount whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\BillingAccountType $accountType
 */
class BillingAccount extends Model
{
    protected $table = 'billing_accounts';
    protected $fillable = ['user_id','acc_type_id'];
    public $timestamps = true;

    public function getBalanceAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setBalanceAttribute($value)
    {
        $this->attributes['balance'] = MoneyAmount::toExternal($value);
    }

    public function accountType()
    {
        return $this->belongsTo(BillingAccountType::class, 'acc_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gerUser()
    {
        return $this->user()->first();
    }
}
