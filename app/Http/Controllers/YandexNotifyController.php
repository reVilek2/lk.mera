<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayService;

class YandexNotifyController extends Controller
{
    public function index(Request $request)
    {
        info('request: '.$request->all());
        if (PayService::processNotificationRequest($request->all())) {
            info('request process: true');
            return PayService::getNotificationResponse(200);
        }
        info('request process: false');
        return PayService::getNotificationResponse(500);
    }
}
