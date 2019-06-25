@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Оплата услуг',
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('payment') }}
@endsection

@section('content')
    @if (session('status'))
        @include('components/noty', [
            'type' => session('status'),
            'text' => session('statusMessage'),
        ])
    @endif
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body">
                    <payment-service :payment-cards="{{json_encode($payment_cards)}}"></payment-service>
                    {{--<form action="{{route('payment.create')}}" method="post">--}}
                        {{--@csrf--}}
                        {{--<input name="amount" type="text">--}}
                        {{--<select name="type" id="" oninput="document.getElementById('paymentType').value = this.value;">--}}
                            {{--<option value="AC">Оплата с банковской карты</option>--}}
                            {{--<option value="PC">Оплата из кошелька в Яндекс.Деньгах</option>--}}
                        {{--</select>--}}
                        {{--<input name="paymentType" value="AC" type="hidden" id="paymentType"/>--}}
                        {{--<input type="submit" value="Заплатить"/>--}}
                    {{--</form>--}}
                </div>
            </div>
        </div>
    </div>
@endsection