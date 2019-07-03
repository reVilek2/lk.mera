<?php namespace App\ModuleSms\Drivers;

use App\ModuleSms\Exceptions\SmsRuException;
use App\ModuleSms\Interfaces\SmsTransportInterface;
use stdClass;

class SmsRuTransport implements SmsTransportInterface
{
    private $api_id;
    private $protocol = 'https';
    private $domain = 'sms.ru';
    private $sender = 'MeraCapital';
    private $count_repeat = 5; //количество попыток достучаться до сервера если он не доступен
    private $test; //1 - Имитирует отправку сообщения для тестирования ваших программ на правильность обработки ответов сервера. (по умолчанию 0)


    function __construct($api_id, $is_test = false) {
        $this->api_id = $api_id;
        $this->test = (int) $is_test;
    }

    /**
     * Совершает отправку СМС сообщения одному или нескольким получателям.
     * @param string $phone
     * @param string $message
     *
     *   $post->to = string - Номер телефона получателя (либо несколько номеров, через запятую — до 100 штук за один запрос). Если вы указываете несколько номеров и один из них указан неверно, то на остальные номера сообщения также не отправляются, и возвращается код ошибки.
     *   $post->msg = string - Текст сообщения в кодировке UTF-8
     *   $post->multi = array('номер получателя' => 'текст сообщения') - Если вы хотите в одном запросе отправить разные сообщения на несколько номеров, то воспользуйтесь этим параметром (до 100 сообщений за 1 запрос). В этом случае, параметры to и text использовать не нужно
     *   $post->from = string - Имя отправителя (должно быть согласовано с администрацией). Если не заполнено, в качестве отправителя будет указан ваш номер.
     *   $post->time = Если вам нужна отложенная отправка, то укажите время отправки. Указывается в формате UNIX TIME (пример: 1280307978). Должно быть не больше 7 дней с момента подачи запроса. Если время меньше текущего времени, сообщение отправляется моментально.
     *   $post->translit = 1 - Переводит все русские символы в латинские. (по умолчанию 0)
     *   $post->test = 1 - Имитирует отправку сообщения для тестирования ваших программ на правильность обработки ответов сервера. При этом само сообщение не отправляется и баланс не расходуется. (по умолчанию 0)
     *   $post->partner_id = int - Если вы участвуете в партнерской программе, укажите этот параметр в запросе и получайте проценты от стоимости отправленных сообщений.
     *
     * @return array|mixed|\stdClass
     * @throws SmsRuException
     */
    public function send(string $phone, string $message) {
        $post = new stdClass();
        $post->to = $phone;
        $post->msg = $message;
        $post->from = $this->sender;

        $url = $this->protocol . '://' . $this->domain . '/sms/send';
        $post->test = $this->test;

        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'send');
    }

    /**
     * Получение состояния баланса
     * @throws SmsRuException
     */
    public function getBalance()
    {
        $url = $this->protocol . '://' . $this->domain . '/my/balance';
        $request = $this->Request($url);
        $response = $this->CheckReplyError($request, 'getBalance');
        if (!$response || !$response->balance){

            throw new SmsRuException('Невозможно получить сведения о балансе.');
        }
        return $response->balance;
    }

    /**
     * Получение текущего состояния вашего дневного лимита.
     * @throws SmsRuException
     */
    public function getLimit() {
        $url = $this->protocol . '://' . $this->domain . '/my/limit';
        $request = $this->Request($url);

        return $this->CheckReplyError($request, 'getLimit');
    }

    /**
     * @param $url
     * @param bool $post
     * @return mixed
     */
    private function Request($url, $post = FALSE) {
        if ($post) {
            $r_post = $post;
        }
        $ch = curl_init($url . "?json=1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if (!$post) {
            $post = new stdClass();
        }

        if (!empty($post->api_id) && $post->api_id=='none'){
        }
        else {
            $post->api_id = $this->api_id;
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query((array) $post));

        $body = curl_exec($ch);
        if ($body === FALSE) {
            $error = curl_error($ch);
        }
        else {
            $error = FALSE;
        }
        curl_close($ch);
        if ($error && $this->count_repeat > 0) {
            $this->count_repeat--;
            return $this->Request($url, $r_post);
        }
        return $body;
    }

    /**
     * @param $res
     * @param $action
     * @return mixed|stdClass
     * @throws SmsRuException
     */
    private function CheckReplyError($res, $action) {
        $error = new stdClass();
        $error->status = "ERROR";
        $error->status_code = "000";
        $error->status_text = "Невозможно установить связь с сервером.";

        $response = null;
        if ($res) {
            $response = json_decode($res);
        }

        if (!$response || !$response->status_code) {
            $response = $error;
        }

        if (!array_key_exists($response->status_code, $this->getSuccessCode())) {
            $this->handleError($response);
        }

        return $response;
    }

    private function getSuccessCode()
    {
        return [
            '100' => 'Команда выполнена успешно (или сообщение принято к отправке)',
            '101' => 'Сообщение передается оператору',
            '102' => 'Сообщение отправлено (в пути)',
            '103' => 'Сообщение доставлено',
        ];
    }

    /**
     * @param stdClass $response
     * @throws SmsRuException
     */
    private function handleError(stdClass $response)
    {
        switch ($response->status_code) {
            case SmsRuException::HTTP_CODE_000:
                throw new SmsRuException('Невозможно установить связь с сервером.');
                break;
            case SmsRuException::HTTP_CODE_104:
                throw new SmsRuException('Сообщение не может быть доставлено: время жизни истекло.');
                break;
            case SmsRuException::HTTP_CODE_105:
                throw new SmsRuException('Сообщение не может быть доставлено: удалено оператором.');
                break;
            case SmsRuException::HTTP_CODE_106:
                throw new SmsRuException('Сообщение не может быть доставлено: сбой в телефоне.');
                break;
            case SmsRuException::HTTP_CODE_107:
                throw new SmsRuException('Сообщение не может быть доставлено: неизвестная причина.');
                break;
            case SmsRuException::HTTP_CODE_108:
                throw new SmsRuException('Сообщение не может быть доставлено: отклонено.');
                break;
            case SmsRuException::HTTP_CODE_130:
                throw new SmsRuException('Сообщение не может быть доставлено: превышено количество сообщений на этот номер в день.');
                break;
            case SmsRuException::HTTP_CODE_131:
                throw new SmsRuException('Сообщение не может быть доставлено: превышено количество одинаковых сообщений на этот номер в минуту.');
                break;
            case SmsRuException::HTTP_CODE_132:
                throw new SmsRuException('Сообщение не может быть доставлено: превышено количество одинаковых сообщений на этот номер в день.');
                break;
            case SmsRuException::HTTP_CODE_200:
                throw new SmsRuException('Неправильный api_id.');
                break;
            case SmsRuException::HTTP_CODE_201:
                throw new SmsRuException('Не хватает средств на лицевом счету.');
                break;
            case SmsRuException::HTTP_CODE_202:
                throw new SmsRuException('Неправильно указан получатель.');
                break;
            case SmsRuException::HTTP_CODE_203:
                throw new SmsRuException('Нет текста сообщения.');
                break;
            case SmsRuException::HTTP_CODE_204:
                throw new SmsRuException('Имя отправителя не согласовано с администрацией.');
                break;
            case SmsRuException::HTTP_CODE_205:
                throw new SmsRuException('Сообщение слишком длинное (превышает 8 СМС).');
                break;
            case SmsRuException::HTTP_CODE_206:
                throw new SmsRuException('Будет превышен или уже превышен дневной лимит на отправку сообщений.');
                break;
            case SmsRuException::HTTP_CODE_207:
                throw new SmsRuException('На этот номер (или один из номеров) нельзя отправлять сообщения, либо указано более 100 номеров в списке получателей.');
                break;
            case SmsRuException::HTTP_CODE_208:
                throw new SmsRuException('Параметр time указан неправильно.');
                break;
            case SmsRuException::HTTP_CODE_209:
                throw new SmsRuException('Вы добавили этот номер (или один из номеров) в стоп-лист.');
                break;
            case SmsRuException::HTTP_CODE_210:
                throw new SmsRuException('Используется GET, где необходимо использовать POST.');
                break;
            case SmsRuException::HTTP_CODE_211:
                throw new SmsRuException('Метод не найден.');
                break;
            case SmsRuException::HTTP_CODE_212:
                throw new SmsRuException('Текст сообщения необходимо передать в кодировке UTF-8 (вы передали в другой кодировке).');
                break;
            case SmsRuException::HTTP_CODE_220:
                throw new SmsRuException('Сервис временно недоступен, попробуйте чуть позже.');
                break;
            case SmsRuException::HTTP_CODE_230:
                throw new SmsRuException('Превышен общий лимит количества сообщений на этот номер в день.');
                break;
            case SmsRuException::HTTP_CODE_231:
                throw new SmsRuException('Превышен лимит одинаковых сообщений на этот номер в минуту.');
                break;
            case SmsRuException::HTTP_CODE_232:
                throw new SmsRuException('Превышен лимит одинаковых сообщений на этот номер в день.');
                break;
            case SmsRuException::HTTP_CODE_300:
                throw new SmsRuException('Неправильный token (возможно истек срок действия, либо ваш IP изменился).');
                break;
            case SmsRuException::HTTP_CODE_301:
                throw new SmsRuException('Неправильный пароль, либо пользователь не найден.');
                break;
            case SmsRuException::HTTP_CODE_302:
                throw new SmsRuException('Пользователь авторизован, но аккаунт не подтвержден (пользователь не ввел код, присланный в регистрационной смс).');
                break;
            default:
                throw new SmsRuException('Unexpected response error code.');
        }
    }
}