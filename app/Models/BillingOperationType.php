<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\BillingOperationType
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingOperationType whereName($value)
 * @mixin \Eloquent
 */
class BillingOperationType extends Model
{
    const INCOMING = 'incoming';
    const OUTGOING = 'outgoing';

    protected $table = 'billing_operation_type';
    public $timestamps = false;
}
