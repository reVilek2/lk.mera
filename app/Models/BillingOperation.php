<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoneyAmount;


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
