<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionStatus extends Model
{
    const PENDING = 'pending';
    const SUCCESS = 'success';
    const ERROR = 'error';

    protected $table = 'billing_transaction_status';
    public $timestamps = false;
}
