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
                    <payment-service :payment-cards="{{json_encode($payment_cards)}}" :document="{{json_encode($document)}}"></payment-service>
                </div>
            </div>
        </div>
    </div>
@endsection