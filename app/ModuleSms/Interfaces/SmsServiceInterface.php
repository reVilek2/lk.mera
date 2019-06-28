<?php namespace App\ModuleSms\Interfaces;

interface SmsServiceInterface
{
    public function getBalance();
    public function send(string $phone, string $message);
}