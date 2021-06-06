<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BillingAccountType
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BillingAccountType whereType($value)
 * @mixin \Eloquent
 */
class BillingAccountType extends Model
{
    const BALANCE = 'balance';
    const VIRTUAL = 'virtual';
    const KASSA_YANDEX = 'kassa_yandex';
    const TINKOFF = 'tinkoff';
    const PAYKEEPER = 'paykeeper';
    const SERVICE = 'service';

    protected $table = 'billing_account_type';
    public $timestamps = false;
}
