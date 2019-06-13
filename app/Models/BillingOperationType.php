<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BillingOperationType extends Model
{
    const INCOMING = 'incoming';
    const OUTGOING = 'outgoing';

    protected $table = 'billing_operation_type';
    public $timestamps = false;
}
