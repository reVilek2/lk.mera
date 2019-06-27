<?php
namespace App\ModulePayment\Drivers;

use App\ModulePayment\Exceptions\YandexPaymentException;
use YandexCheckout\Client;
use App\ModulePayment\Interfaces\PaymentTransportInterface;
use YandexCheckout\Helpers\UUID;
/**
 * Class to work with Yandex.Kassa
 */
class YandexKassa implements PaymentTransportInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * Shop ID
     *
     * @var int
     */
    private $shopId;
    /**
     * Shop secret key
     *
     * @var string
     */
    private $shopSecret;
    /**
     * Yandex.Kassa constructor.
     *
     * @param int $shopId
     * @param int $shopSecret
     */
    public function __construct($shopId = null, $shopSecret = null)
    {
        $this->setShopId($shopId)->setShopPassword($shopSecret);
    }
    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }
    /**
     * Set Shop id
     *
     * @param int $shopId
     *
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
        return $this;
    }

    /**
     * Get payment data
     *
     * @param mixed $params
     *
     * @param null $idempotencyKey
     * @return string
     * @throws YandexPaymentException
     */
    public function createPayment($params, $idempotencyKey = null)
    {
        try {
            $response = $this->getClient()->createPayment($this->prepareParams($params), $idempotencyKey);
        }
        catch (\Exception $exception) {

            throw YandexPaymentException::parse($exception);
        }
        return $response;
    }

    /**
     * @param $payment_id
     * @return \YandexCheckout\Model\PaymentInterface
     * @throws YandexPaymentException
     */
    public function getPayInfo($payment_id)
    {
        try {
            return $this->getClient()->getPaymentInfo($payment_id);
        }
        catch (\Exception $exception) {

            throw YandexPaymentException::parse($exception);
        }
    }

    /**
     * Get shop secret key
     *
     * @return string
     */
    public function getShopPassword()
    {
        return $this->shopSecret;
    }
    /**
     * Set shop secret key
     *
     * @param string $shopSecret
     *
     * @return $this;
     */
    public function setShopPassword($shopSecret)
    {
        $this->shopSecret = $shopSecret;
        return $this;
    }

    /**
     * Create Kassa.Yandex client
     *
     * @return Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client();
            $this->client->setAuth($this->getShopId(), $this->getShopPassword());
        }
        return $this->client;
    }
    /**
     * Prepare parameters
     *
     * @param array $params
     *
     * @return array
     */
    public function prepareParams($params)
    {
        return $params;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function uniqid()
    {
        return UUID::v4();
    }
}