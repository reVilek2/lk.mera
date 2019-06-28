<?php namespace App\ModuleSms\Drivers;

use App\ModuleSms\Interfaces\SmsServiceInterface;
use App\ModuleSms\Interfaces\SmsTransportInterface;
use App\ModuleSms\SmsProvider;

/**
 * Class SmsRuDriver
 * @package App\ModuleSms\Drivers
 */
class SmsRuDriver implements SmsServiceInterface
{
    /**
     * Module config
     *
     * @var array
     */
    private $config;

    /**
     * @var SmsTransportInterface
     */
    private $transport;

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * Set driver configuration
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get transport
     *
     * @return SmsTransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }
    /**
     * Set transport
     *
     * @param SmsTransportInterface $transport
     *
     * @return $this
     */
    public function setTransport(SmsTransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Get name of payment service
     *
     * @return string
     */
    public function getName()
    {
        return SmsProvider::SMS_RU;
    }

    public function getBalance()
    {
        return $this->getTransport()->getBalance();
    }

    public function send(string $phone, string $message)
    {
        return $this->getTransport()->send($phone, $message);
    }
}