<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    const MANUAL_IN = 'manual_in';
    const MANUAL_OUT = 'manual_out';
    const CARD_IN = 'card_in';
    const CARD_OUT = 'card_out';
    const SERVICE_IN = 'service_in';
    const SERVICE_OUT = 'service_out';

    protected $table = 'billing_transaction_type';
    public $timestamps = false;
}
