@extends('layouts.app')

@section('title')
    @include('components/title', [
        'title'=>'Пользователи',
        'description'=>''
     ])
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render('users') }}
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
                <users-table :users="{{$users}}" :users_count="{{$users_count}}"></users-table>
            </div>
        </div>
    </div>
@endsection