<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAccountType extends Model
{
    const BALANCE = 'balance';
    const VIRTUAL = 'virtual';
    const KASSA_YANDEX = 'kassa_yandex';
    const SERVICE = 'service';

    protected $table = 'billing_account_type';
    public $timestamps = false;
}
