<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayService;
use YandexCheckout\Request\Payments\PaymentResponse;

class YandexNotifyController extends Controller
{
    public function index(Request $request)
    {
        if (PayService::processNotificationRequest(new PaymentResponse($request->input('object')))) {
            return PayService::getNotificationResponse(200);
        }

        return PayService::getNotificationResponse(500);
    }
}
