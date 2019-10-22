<?php
namespace App\ModulePayment\Drivers\Tinkoff;

use App\ModulePayment\Interfaces\PaymentTransportInterface;
use App\ModulePayment\Traits\TransportTrait;

require_once 'TinkoffMerchantAPI.php';

class TinkoffProtocol extends \TinkoffMerchantAPI implements PaymentTransportInterface
{
    use TransportTrait;

    /**
     * Get payment URL
     *
     * @param mixed $params
     *
     * @return string
     * @throws \Exception
     */
    public function getPaymentUrl($params)
    {
        $this->init($params);
        if ($this->error != '') {
            throw new \Exception($this->error);
        }
        return $this->paymentUrl;
    }

    /**
     * Get payment URL
     *
     * @param mixed $params
     *
     * @return string
     * @throws \Exception
     */
    public function createPayment($params)
    {
        $this->init($params);
        if ($this->error != '') {
            throw new \Exception($this->error);
        }

        return $this;
    }

    /**
     * Get payment URL
     *
     * @param mixed $params
     *
     * @return string
     * @throws \Exception
     */
    public function createCharge($params)
    {
        $this->charge($params);
        if ($this->error != '') {
            throw new \Exception($this->error);
        }

        return $this;
    }

    /**
     * Get payment URL
     *
     * @param mixed $params
     *
     * @return string
     * @throws \Exception
     */
    public function getPayInfo($params)
    {
        $this->getState ($params);
        if ($this->error != '') {
            throw new \Exception($this->error);
        }

        return $this;
    }

    /**
     * Validate params
     *
     * @param mixed $params
     *
     * @return bool
     */
    public function validate($params)
    {
        $result = false;

        if (isset($params['Token'])) {
            $token = $params['Token'];
            unset($params['Token']);
            if ($token != '' && $this->genToken($params) == $token) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get payment ID
     *
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Prepare response on notification request
     *
     * @param mixed $requestData
     * @param int   $errorCode
     *
     * @return string
     */
    public function getNotificationResponse($requestData, $errorCode)
    {
        return $errorCode > 0 ? response('ERROR') : response('OK');
    }

    /**
     * Prepare response on check request
     *
     * @param array $requestData
     * @param int   $errorCode
     *
     * @return string
     */
    public function getCheckResponse($requestData, $errorCode)
    {
        return $errorCode > 0 ? response('ERROR') : response('OK');
    }

}
