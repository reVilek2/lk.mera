<?php
namespace App\ModulePayment\Traits;

use App\ModulePayment\Interfaces\PaymentTransportInterface;

trait DriversTrait
{
    /**
     * @var PaymentTransportInterface
     */
    protected $transport;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $response;

    public function getPaymentMethod($type)
    {
        $map = [
            self::PAYMENT_TYPE_CARD         => 'bank_card',
            self::PAYMENT_TYPE_CASH         => 'cash',
            self::PAYMENT_TYPE_MOBILE       => 'mobile_balance',
            self::PAYMENT_TYPE_QIWI         => 'qiwi',
            self::PAYMENT_TYPE_SBERBANK     => 'sberbank',
            self::PAYMENT_TYPE_YANDEX_MONEY => 'yandex_money',
            self::PAYMENT_TYPE_ALFABANK     => 'alfabank',
        ];
        return isset($map[$type]) ? $map[$type] : $map[self::PAYMENT_TYPE_CARD];
    }

    private function substrCardNum(string $val)
    {
        return substr($val, 0, 4);
    }

    /**
     * @inheritDoc
     */
    public function setTransport(PaymentTransportInterface $protocol)
    {
        $this->transport = $protocol;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function setResponse($data)
    {
        $data['DateTime'] = date('Y-m-d H:i:s');
        $this->response = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResponseParam($name, $default = '')
    {
        return isset($this->response[$name]) ? $this->response[$name] : $default;
    }

}
