<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoneyAmount;

class Transaction extends Model
{
    protected $table = 'billing_transactions';
    protected $fillable = ['status_id','type_id','user_id','amount','receiver_acc_id','sender_acc_id'];
    public $timestamps = true;

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
}
