@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=> $pageTitle,
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('reports') }}
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
                <documents :documents="{{$documents}}"
                                 :documents_count="{{$documents_count}}"
                                 :managers="{{$managers}}"
                                 agent-type="{{ layout_type() }}"></documents>
            </div>
        </div>
    </div>
@endsection
