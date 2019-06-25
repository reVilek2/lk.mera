<?php

namespace App\Http\Controllers;

use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\Services\Page;
use Illuminate\Http\Request;
use PayService;
use Validator;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        Page::setTitle('Оплата услуг | MeraCapital');
        Page::setDescription('Пополнение баланса');

        return view('payments.index');
    }

    public function create(Request $request)
    {
        Page::setTitle('Оплата услуг | MeraCapital');
        Page::setDescription('Пополнение баланса');

        $amount = $request->input('amount');
        $paymentType = $request->input('payment_type');
        $saveCard = $request->input('save_card');

        $validation = Validator::make($request->all(), [
            'save_card'=> 'required|boolean',
            'payment_type'=> 'required|string',
            'amount'=> 'required|regex:/^\d*(\.\d+)?$/',
        ]);

        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            if (!array_key_exists($paymentType, PaymentServiceInterface::WHITE_LIST_TYPE)) {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['payment_type'=> ['Не удалось распознать тип платежа']]
                ], 200);
            }

            $idempotency_key = PayService::uniqid();

            $paymentData = PayService::getPaymentData(
                $amount,
                $paymentType,
                $description = 'Пополнение баланса через "Yandex Kassa"',
                $successReturnUrl = route('payment.check').'?pay_key='.$idempotency_key,
                $metadata = [
                    'idempotency_key'=> $idempotency_key,
                    'save_card' => $saveCard
                ],
                $extraParams = []
            );

            PayService::makePayment($paymentData);
            $payLink  = PayService::getPayLink($paymentData);

            return response()->json([
                'status'=>'success',
                'pay_link' => $payLink,
            ], 200);

        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkPayment(Request $request)
    {
        Page::setTitle('Проверка платежа | MeraCapital');
        Page::setDescription('Проверка платежа');

        $pay_key = $request->input('pay_key', null);
        if ($pay_key) {
            /** @var ModelPaymentInterface $payment */
            $payment = PayService::getPaymentByUniqueKey($pay_key);
            if ($payment && $payment->status === ModelPaymentInterface::STATUS_PENDING) {
                PayService::checkPayment($payment);
            }
        }

        return redirect()->route('finances');
    }
}
