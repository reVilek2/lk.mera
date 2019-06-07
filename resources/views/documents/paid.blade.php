@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Оплата документа',
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('documents') }}
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
                <div class="box-header">
                    <h3 class="box-title">Оплата документа</h3>
                </div>
                <div class="box-body">
                    Документ: {{$document->name}}
                </div>
            </div>
        </div>
    </div>
@endsection