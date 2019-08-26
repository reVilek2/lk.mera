@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Рекомендации',
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('recommendations') }}
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
                <recommendations-list
                    :recommendations="{{$recommendations}}"
                    :recommendations_count="{{$recommendations_count}}"
                    :managers="{{$managers}}"
                >
                </recommendations-list>
            </div>
        </div>
    </div>
@endsection
