<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayService;
use YandexCheckout\Request\Payments\PaymentResponse;

class PaymentNotifyController extends Controller
{
    public function yandex(Request $request)
    {
        if (PayService::processNotificationRequest(new PaymentResponse($request->input('object')))) {
            return PayService::getNotificationResponse(200);
        }

        return PayService::getNotificationResponse(500);
    }

    public function tinkoff(Request $request)
    {
        $notification = $request->all();
        info($notification);
        if (PayService::processNotificationRequest($notification)) {
            return PayService::getNotificationResponse(200);
        }

        return PayService::getNotificationResponse(500);
    }
}
