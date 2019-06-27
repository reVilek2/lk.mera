<?php

namespace App\Exceptions;

use Exception;

class BillingException extends Exception
{
    public static function unknownTransactionTypeInMapping(string $name)
    {
        return new static("There is no mapping transaction type name `{$name}`");
    }
    public static function unknownTransactionOperationTypeInMapping(string $name)
    {
        return new static("There is no mapping for operation by transaction type name `{$name}`");
    }
    public static function unknownBillingAccountType(string $name)
    {
        return new static("Unknown billing account type `{$name}`");
    }
    public static function unknownTransactionStatus(string $name)
    {
        return new static("Unknown transaction status `{$name}`");
    }
    public static function unknownTransactionType(string $name)
    {
        return new static("Unknown transaction type `{$name}`");
    }
    public static function unknownBillingOperationType(string $name)
    {
        return new static("Unknown billing operation type `{$name}`");
    }
    public static function notEnoughFunds()
    {
        return new static("Not enough funds to perform the operation");
    }
}