<?php
namespace App\ModulePayment\Interfaces;

interface ModelPaymentInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_CANCELED = 'canceled';
}