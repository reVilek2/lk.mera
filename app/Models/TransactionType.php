<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TransactionType
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionType whereName($value)
 * @mixin \Eloquent
 */
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
