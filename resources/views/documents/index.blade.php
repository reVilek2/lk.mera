@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Документы',
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
                    <h3 class="box-title">Таблица документов</h3>
                </div>
                <div class="box-body">
                    <documents-table :documents="{{$documents}}"
                                     :managers="{{$managers}}"
                                     :current-user="{{Auth::user()}}"></documents-table>
                </div>
            </div>
        </div>
    </div>
@endsection