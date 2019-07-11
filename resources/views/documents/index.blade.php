@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Отчеты',
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
                <documents-table :documents="{{$documents}}"
                                 :documents_count="{{$documents_count}}"
                                 :managers="{{$managers}}"></documents-table>
            </div>
        </div>
    </div>
@endsection