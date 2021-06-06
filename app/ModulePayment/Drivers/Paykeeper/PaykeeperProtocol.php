<?php


namespace App\ModulePayment\Drivers\Paykeeper;


use App\ModulePayment\Interfaces\PaymentTransportInterface;

class PaykeeperProtocol implements PaymentTransportInterface
{
    private $server;
    private $user;
    private $pass;

    public function __construct($server, $user, $pass)
    {
        $this->server = $server;
        $this->user = $user;
        $this->pass = $pass;
    }

    function getHeaders()
    {
        # Basic-авторизация передаётся как base64
        $base64 = base64_encode("$this->user:$this->pass");
        $headers = Array();
        array_push($headers, 'Content-Type: application/x-www-form-urlencoded');

        # Подготавливаем заголовок для авторизации
        array_push($headers, 'Authorization: Basic '.$base64);
        return $headers;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function getToken()
    {

        # Готовим первый запрос на получение токена безопасности
        $uri = "/info/settings/token/";

        # Для сетевых запросов в этом примере используется cURL
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->server.$uri);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_HEADER, false);

        # Инициируем запрос к API
        $response = curl_exec($curl);
        $php_array = json_decode($response, true);

        # В ответе должно быть заполнено поле token, иначе - ошибка
        if (!isset($php_array['token'])) {
            throw new \Exception('bad credentials');
        }

        return $php_array['token'];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function createPayment($params)
    {
        # Готовим запрос 3.4 JSON API на получение счёта
        $uri = "/change/invoice/preview/";

        # Формируем список POST параметров
        $request = http_build_query(array_merge($params, array('token' => $this->getToken())));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->server.$uri);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);


        $response = json_decode(curl_exec($curl), true);
        # В ответе должно быть поле invoice_id, иначе - ошибка
        if (isset($response['invoice_id'])) {
            $invoice_id = $response['invoice_id'];
        } else {
            throw new \Exception('bad payment');
        }

        # В этой переменной прямая ссылка на оплату с заданными параметрами
        $link = "https://{$this->server}/bill/{$invoice_id}/";
        $response['link'] = $link;
        return $response;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function getPayInfo($payment_id)
    {
        # В примере используется библиотека cURL
        $curl = curl_init();

        # Готовим запрос 3.1 JSON API на получение счёта
        $uri = "/info/invoice/byid/?id=$payment_id";

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->server.$uri);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_HEADER, false);

        # В ответе сервера должно быть поле status. Иначе - ошибка
        $response = json_decode(curl_exec($curl), true);

        if (!isset($response['status'])) {
            throw new \Exception('bad response');
        }

        return $response;
    }
}
