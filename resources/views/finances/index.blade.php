@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Оплата услуг',
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('finances') }}
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
                    <finance-service></finance-service>
                </div>
            </div>
        </div>
    </div>
@endsection