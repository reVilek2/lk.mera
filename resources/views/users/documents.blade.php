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
                <user-files-table :files="{{$files}}"
                                  :files_count="{{$files_count}}"></user-files-table>
            </div>
        </div>
    </div>
@endsection