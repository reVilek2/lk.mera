<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAccount extends Model
{
    protected $table = 'billing_accounts';
    protected $fillable = ['user_id','acc_type_id'];
    public $timestamps = true;
}
