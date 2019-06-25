<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayService;

class YandexNotifyController extends Controller
{
    public function index(Request $request)
    {
        if (PayService::processNotificationRequest($request->all())) {

            return PayService::getNotificationResponse(200);
        }

        return PayService::getNotificationResponse(500);
    }
}
