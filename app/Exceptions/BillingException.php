<?php

namespace App\Exceptions;

use Exception;

class BillingException extends Exception
{
    public static function unknownTransactionTypeInMapping(string $name)
    {
        return new static("There is no mapping transaction type name `{$name}`");
    }
}