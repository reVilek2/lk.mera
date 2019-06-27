<?php

namespace App\Exceptions;

use Exception;

class DocumentException extends Exception
{
    public static function issetDocumentTransaction()
    {
        return new static("Оплата невозможна. Платеж на оплату уже существует.");
    }
}