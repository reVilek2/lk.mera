<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TransactionStatus
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransactionStatus whereName($value)
 * @mixin \Eloquent
 */
class TransactionStatus extends Model
{
    const PENDING = 'pending';
    const SUCCESS = 'success';
    const ERROR = 'error';

    protected $table = 'billing_transaction_status';
    public $timestamps = false;
}
