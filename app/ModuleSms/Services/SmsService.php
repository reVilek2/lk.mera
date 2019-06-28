<?php namespace App\ModuleSms\Services;

use App\ModuleSms\Interfaces\SmsServiceInterface;

class SmsService implements SmsServiceInterface
{
    /**
     * Available drivers
     *
     * @var array
     */
    private $drivers = [];

    /**
     * Current driver
     *
     * @var SmsServiceInterface
     */
    private $currentDriver;

    /**
     * Name of current driver
     *
     * @var string
     */
    private $currentDriverName;

    /**
     * Set drivers
     *
     * @param array $drivers
     *
     * @return $this
     */
    public function setDrivers(array $drivers)
    {
        $this->drivers = $drivers;

        return $this;
    }

    /**
     * Get drivers
     *
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Get driver by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getDriver($name)
    {
        return $this->getDrivers()[$name];
    }

    /**
     * Set current driver
     *
     * @param string $name
     *
     * @return $this
     */
    public function setCurrentDriver($name)
    {
        $this->currentDriverName = $name;

        $this->makeCurrentDriver($name);

        return $this;
    }

    /**
     * Build driver
     *
     * @param string $name
     *
     * @return $this
     */
    protected function makeCurrentDriver($name)
    {
        $this->currentDriver = \App::make($this->getDriver($name), [
            'config' => config('sms.' . $name),
        ]);

        return $this;
    }

    /**
     * Get current driver name
     *
     * @return SmsServiceInterface
     */
    public function getCurrentDriver()
    {
        return $this->currentDriver;
    }

    /**
     * Get name of current driver
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->currentDriverName;
    }

    public function getBalance()
    {
        return $this->getCurrentDriver()->getBalance();
    }

    public function send(string $phone, string $message)
    {
        return $this->getCurrentDriver()->send($phone, $message);
    }
}