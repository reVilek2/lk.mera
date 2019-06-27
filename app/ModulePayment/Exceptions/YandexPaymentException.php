<?php
namespace App\ModulePayment\Exceptions;

use Exception;

class YandexPaymentException extends Exception
{
    const HTTP_CODE_400 = 400;
    const HTTP_CODE_401 = 401;
    const HTTP_CODE_403 = 403;
    const HTTP_CODE_404 = 404;
    const HTTP_CODE_429 = 429;
    const HTTP_CODE_500 = 500;

    public static function invalid_request(string $message)
    {
        return new static("Не удалось произвести оплату. Поробуйте оплатить онлайн.");
    }
    public static function invalid_credentials(string $message)
    {
        return new static("Неверный идентификатор вашего аккаунта в Яндекс.Кассе или секретный ключ.");
    }
    public static function forbidden(string $name)
    {
        return new static("Не хватает прав для совершения операции.");
    }
    public static function not_found(string $name)
    {
        return new static("Ресурс не найден.");
    }
    public static function too_many_requests(string $name)
    {
        return new static("Превышен лимит запросов в единицу времени.");
    }
    public static function internal_server_error(string $name)
    {
        return new static("Технические неполадки. Повторите запрос позднее.");
    }
    public static function unexpected_response(string $name)
    {
        return new static("Технические неполадки. Повторите запрос позднее.");
    }


    public static function parse(\Exception $exception)
    {
        // лог
        info('yandex paynet error: '.$exception->getMessage());

        switch ($exception->getCode()) {
            case self::HTTP_CODE_400:
                return self::invalid_request($exception);
                break;
            case self::HTTP_CODE_401:
                return self::invalid_credentials($exception);
                break;
            case self::HTTP_CODE_403:
                return self::forbidden($exception);
                break;
            case self::HTTP_CODE_404:
                return self::not_found($exception);
                break;
            case self::HTTP_CODE_429:
                return self::too_many_requests($exception);
                break;
            case self::HTTP_CODE_500:
                return self::internal_server_error($exception);
                break;
            default:
                if ($exception->getCode() > 399) {
                    return self::unexpected_response($exception);
                }
        }
    }
}