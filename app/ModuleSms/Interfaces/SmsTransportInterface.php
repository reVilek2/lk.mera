<?php namespace App\ModuleSms\Interfaces;

interface SmsTransportInterface
{
    public function getBalance();
    public function send(string $phone, string $message);
}