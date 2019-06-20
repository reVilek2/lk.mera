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
                                     :documents_count="{{$documents_count}}"
                                     :managers="{{$managers}}"></documents-table>
                    {{--@if ($documents_count === 0 && !Auth::user()->hasRole('admin|manager'))--}}
                        {{--<div class="callout callout-info">--}}
                            {{--<h4>I am an info callout!</h4>--}}

                            {{--<p>У вас нет ни одного документа</p>--}}
                        {{--</div>--}}
                    {{--@else--}}
                        {{----}}
                    {{--@endif--}}
                </div>
            </div>
        </div>
    </div>
@endsection